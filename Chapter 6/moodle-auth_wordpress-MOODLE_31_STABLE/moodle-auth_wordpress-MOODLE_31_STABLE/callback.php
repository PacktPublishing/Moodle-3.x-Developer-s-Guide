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

require_once('../../config.php');
require_once($CFG->dirroot . '/auth/wordpress/auth.php');

defined('MOODLE_INTERNAL') || die();

// checks to ensure that we have come here via WordPress...
if(!isset($_REQUEST['oauth_verifier'])) {
    print_error('missingverifier', 'auth_wordpress');
}

// get the wordpress plugin instance

$authplugin = get_auth_plugin('wordpress');

if(isset($authplugin)) {
    
    // call the callback handler
    $authplugin->callback_handler();
}

