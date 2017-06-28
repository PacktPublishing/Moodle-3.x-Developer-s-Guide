<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authenticates against a WordPress installation using OAuth 1.0a.
 *
 * @package auth_wordpress
 * @author Ian Wild
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot . '/auth/wordpress/OAuth.php');
require_once($CFG->dirroot . '/auth/wordpress/BasicOAuth.php');

use \OAuth1\BasicOauth;
 
/**
 * Plugin for WordPress authentication.
 */
class auth_plugin_wordpress extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'wordpress';
        $this->config = get_config('auth/wordpress');
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function auth_plugin_wordpress() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id, 'auth'=>'wordpress'))) {
            return true;
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }

    /**
     * Returns true if this authentication plugin doesn't support local passwords -  which we don't so we 
     * store 'not_cached' in the user table.
     * 
     * {@inheritDoc}
     * @see auth_plugin_base::prevent_local_passwords()
     * 
     * return bool
     */
    function prevent_local_passwords() {
        return true;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     * 
     * @return @bool
     */
    function process_config($config) {
        // Set to defaults if undefined
        if (!isset($config->wordpress_host)) {
            $config->wordpress_host = '';
        }
        if (!isset($config->client_key)) {
            $config->client_key = '';
        }
        if (!isset($config->client_secret)) {
            $config->client_secret = '';
        }
        set_config('wordpress_host', trim($config->wordpress_host), 'auth/wordpress');
        set_config('client_key', trim($config->client_key), 'auth/wordpress');
        set_config('client_secret', trim($config->client_secret), 'auth/wordpress');
        
        return true;
    }
    
    /**
     * Will get called before the login page is shown. 
     *
     */
    function loginpage_hook() {
        global $CFG, $SESSION;    
    
        if(isset($CFG->disablewordpressauth) && ($CFG->disablewordpressauth == true)) {
            return;
        }
        
        // Only authenticate against WordPress if the user has clicked on a link to a protected resource
        if(!isset($SESSION->wantsurl)) {
            return;
        }
        
        $client_key = $this->config->client_key;
        $client_secret = $this->config->client_secret;
        $wordpress_host = $this->config->wordpress_host;
       
        if( (strlen($wordpress_host) > 0) && (strlen($client_key) > 0) && (strlen($client_secret) > 0) ) {
            // kick ff the authentication process
            $connection = new BasicOAuth($client_key, $client_secret);
       
            // strip the trailing slashes from the end of the host URL to avoid any confusion (and to make the code easier to read)
            $wordpress_host = rtrim($wordpress_host, '/');
            
            $connection->host = $wordpress_host . "/wp-json";
            $connection->requestTokenURL = $wordpress_host . "/oauth1/request";
       
            $callback = $CFG->wwwroot . '/auth/wordpress/callback.php';
            $tempCredentials = $connection->getRequestToken($callback);
       
            $_SESSION['oauth_token'] = $tempCredentials['oauth_token'];
            $_SESSION['oauth_token_secret'] = $tempCredentials['oauth_token_secret'];
       
            $connection->authorizeURL = $wordpress_host . "/oauth1/authorize";
       
            $redirect_url = $connection->getAuthorizeURL($tempCredentials);
       
            header('Location: ' . $redirect_url);
            die;
        }// if   
    }
    
    /**
     * Called externally as the third and final leg of three legged authentication. This function performs the final
     * Moodle authentication. 
     * 
     */
    function callback_handler() {
        global $CFG, $DB, $SESSION;
        
        $client_key = $this->config->client_key;
        $client_secret = $this->config->client_secret;
        $wordpress_host = $this->config->wordpress_host;
        
        // strip the trailing slashes from the end of the host URL to avoid any confusion (and to make the code easier to read)
        $wordpress_host = rtrim($wordpress_host, '/');
        
        // at this stage we have been provided with new permanent token
        $connection = new BasicOAuth($client_key, $client_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
        
        $connection->host = $wordpress_host . "/wp-json";
        
        $connection->accessTokenURL = $wordpress_host . "/oauth1/access";
        
        $tokenCredentials = $connection->getAccessToken($_REQUEST['oauth_verifier']);
        
        if(isset($tokenCredentials['oauth_token']) && isset($tokenCredentials['oauth_token_secret'])) {
        
            $perm_connection = new BasicOAuth($client_key, $client_secret, $tokenCredentials['oauth_token'],
                    $tokenCredentials['oauth_token_secret']);
            
            $account = $perm_connection->get($wordpress_host . '/wp-json/wp/v2/users/me?context=edit');
            
            if(isset($account)) {
                // firstly make sure there isn't an email collision:
                if($user = $DB->get_record('user', array('email'=>$account->email))) {
                    if($user->auth != 'wordpress') {
                        print_error('usercollision', 'auth_wordpress');
                    }
                }
                
                // check to determine if a user has already been created...     
                if($user = authenticate_user_login($account->username, $account->username)) {
                    // TODO update the current user with the latest first name and last name pulled from WordPress?
        
                    if (user_not_fully_set_up($user, false)) {
                        $urltogo = $CFG->wwwroot.'/user/edit.php?id='.$user->id.'&amp;course='.SITEID;
                        // We don't delete $SESSION->wantsurl yet, so we get there later
        
                    }
                } else {
                    require_once($CFG->dirroot . '/user/lib.php');
                    
                    // we need to configure a new user account
                    $user = new stdClass();
                    
                    $user->mnethostid = $CFG->mnet_localhost_id;
                    $user->confirmed = 1;
                    $user->username = $account->username;
                    $user->password = AUTH_PASSWORD_NOT_CACHED;
                    $user->firstname = $account->first_name;
                    $user->lastname = $account->last_name;
                    $user->email = $account->email;
                    $user->description = $account->description;
                    $user->auth = 'wordpress';
                    
                    $id = user_create_user($user, false);
                    
                    $user = $DB->get_record('user', array('id'=>$id));
                }
                
                complete_user_login($user);
                
                if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
                    $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
                    unset($SESSION->wantsurl);
                
                } else {
                    $urltogo = $CFG->wwwroot.'/';      /// Go to the standard home page
                    unset($SESSION->wantsurl);         /// Just in case
                }
                
                /// Go to my-moodle page instead of homepage if defaulthomepage enabled
                if (!has_capability('moodle/site:config',context_system::instance()) and !empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_MY and !isguestuser()) {
                    if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot.'/' or $urltogo == $CFG->wwwroot.'/index.php') {
                        $urltogo = $CFG->wwwroot.'/my/';
                    }
                }
                
                redirect($urltogo);
                
                exit;
            }
        }
    }

}
