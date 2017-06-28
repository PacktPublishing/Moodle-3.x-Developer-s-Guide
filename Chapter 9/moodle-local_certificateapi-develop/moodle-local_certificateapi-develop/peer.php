<?php
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
 * An object to represent lots of information about an RPC-peer machine
 *
 * @author  Ian Wild - based on work by Donal McMullan  donal@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package local_certificateapi
 */

require_once '../../config.php';
require_once($CFG->libdir . '/filelib.php'); // download_file_content() used here

class certificateapi_peer {

    var $id                     = 0;
    var $deleted                = 0;
    var $ip_address             = '';
    var $wwwroot                = '';
    var $fullname               = '';
    var $last_connect_time      = 0;
    var $clientid               = 0;
    var $public_key             = '';
    var $public_key_validfrom   = 0;
    var $public_key_expires     = 0;
    var $permissions            = '';
    
    function certificateapi_peer() {
        return true;
    }
    
    function load_peer_from_clientid($clientid = '') {
        global $DB;
        
        $sql = "
                SELECT
                    h.*
                FROM
                    {certificateapi_host} h
                WHERE
                    h.clientid = ?";
        
        if ($hostinfo = $DB->get_record_sql($sql, array($clientid))) {
            $this->populate($hostinfo);
            return true;
        }
        
        return false;
    }

    function delete() {
        global $DB;
        
        return $DB->delete_records('certificateapi_host', array('id' => $this->id));
    }
    
    function check_credentials($key) {
        $credentials = openssl_x509_parse($key);
        if ($credentials == false) {
            $this->error[] = array('code' => 3, 'text' => get_string("nonmatchingcert", 'mnet', array('','')));
            return false;
        } elseif (array_key_exists('subjectAltName', $credentials['subject']) && $credentials['subject']['subjectAltName'] != $this->wwwroot) {
            $a['subject'] = $credentials['subject']['subjectAltName'];
            $a['host'] = $this->wwwroot;
            $this->error[] = array('code' => 5, 'text' => get_string("nonmatchingcert", 'mnet', $a));
            return false;
        } elseif ($credentials['subject']['CN'] != $this->wwwroot) {
            $a['subject'] = $credentials['subject']['CN'];
            $a['host'] = $this->wwwroot;
            $this->error[] = array('code' => 4, 'text' => get_string("nonmatchingcert", 'mnet', $a));
            return false;
        } else {
            if (array_key_exists('subjectAltName', $credentials['subject'])) {
                $credentials['wwwroot'] = $credentials['subject']['subjectAltName'];
            } else {
                $credentials['wwwroot'] = $credentials['subject']['CN'];
            }
            return $credentials;
        }
    }

    function commit() {
        global $DB;
        $obj = new stdClass();
        
        $obj->deleted               = $this->deleted;
        $obj->ip_address            = $this->ip_address;
        $obj->wwwroot               = $this->wwwroot;
        $obj->fullname              = $this->fullname;
        $obj->last_connect_time     = $this->last_connect_time;
        $obj->clientid              = $this->clientid;
        $obj->public_key            = $this->public_key;
        $obj->public_key_validfrom  = $this->public_key_validfrom;
        $obj->public_key_expires    = $this->public_key_expires;
        $obj->permissions           = $this->permissions;
        
        if (isset($this->id) && $this->id > 0) {
            $obj->id = $this->id;
            return $DB->update_record('certificateapi_host', $obj);
        } else {
            $this->id = $DB->insert_record('certificateapi_host', $obj);
            return $this->id;
        }
    }

    function touch() {
        $this->last_connect_time = time();
        $this->commit();
    }

    function set_clientid($clientid) {
        global $DB;
        
        if(strlen($clientid) > 80) {
            return false;
        }
        
        // clientid must be unique
        $sql = "
                SELECT
                    h.*
                FROM
                    {certificateapi_host} h
                WHERE
                    h.clientid = ?";
        
        if ($hostinfo = $DB->get_record_sql($sql, array($clientid))) {
            $this->populate($hostinfo);
            return false;
        }
        
        $this->clientid = $clientid;
            
        return true;
    }

    function set_wwwroot($wwwroot) {        
        
        $this->wwwroot = $wwwroot;
        
        return true;
    }
    
    function set_fullname($name) {
    
        $this->fullname = $name;
    
        return true;
    }
    
    function set_key($key) {
    
        $this->public_key = $key;
    
        return true;
    }
    
    function set_validfrom($validfrom) {
    
        $this->public_key_validfrom = $validfrom;
    
        return true;
    }
    
    function set_validto($expires) {
        
        $this->public_key_expires = $expires;
    
        return true;
    }

    function set_id($id) {
        global $CFG, $DB;

        if (clean_param($id, PARAM_INT) != $id) {
            $this->errno[]  = 1;
            $this->errmsg[] = 'Your id ('.$id.') is not legal';
            return false;
        }

        $sql = "
                SELECT
                    h.*
                FROM
                    {certificateapi_host} h
                WHERE
                    h.id = ?";

        if ($hostinfo = $DB->get_record_sql($sql, array($id))) {
            $this->populate($hostinfo);
            return false;
        }
        return true;
    }

    function populate($hostinfo) {
        $this->id                    = $hostinfo->id;
        $this->ip_address            = $hostinfo->ip_address;
        $this->wwwroot               = $hostinfo->wwwroot;
        $this->fullname              = $hostinfo->fullname;
        $this->last_connect_time     = $hostinfo->last_connect_time;
        $this->clientid              = $hostinfo->clientid;
        $this->public_key            = $hostinfo->public_key;
        $this->public_key_expires    = $hostinfo->public_key_expires;
        $this->permissions           = $hostinfo->permissions;
        
        $this->bootstrapped = true;
    }

    function get_public_key() {
        if (isset($this->public_key_ref)) return $this->public_key_ref;
        $this->public_key_ref = openssl_pkey_get_public($this->public_key);
        return $this->public_key_ref;
    }
}
