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
 * Page to allow the administrator to configure API hosts, and add new ones
 *
 * @package    certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once($CFG->dirroot . '/local/certificateapi/peer.php');
require_once($CFG->dirroot . '/local/certificateapi/edit_peer_form.php');

global $DB;

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

// Initialize variables.
$clientid = optional_param('clientid', '', PARAM_TEXT);
$hostname = optional_param('hostname', '', PARAM_TEXT);
$fullname = optional_param('fullname', '', PARAM_TEXT);
$validfrom = optional_param('validfrom', 0, PARAM_INT);
$validto = optional_param('validto', 0, PARAM_INT);
$publickey = optional_param('public_key', '', PARAM_TEXT);

$peer = new certificateapi_peer();

$id = optional_param('id', -1, PARAM_INT);
if($id > -1) {
    // ... then can we load host from DB?
	$result = $peer->set_id($id);
    if($result == false) {
        // TODO: warn that this host is missing?
    }
} else {
    // set up peer with passed parameters 
	$peer->set_clientid($clientid);
	$peer->set_wwwroot($hostname);
	$peer->set_fullname($fullname);
	$peer->set_validfrom($validfrom);
	$peer->set_validto($validto);
	$peer->set_key($publickey);
    
}

$PAGE->set_context($context);
$PAGE->set_url('/local/certificateapi/edit_peer.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'local_certificateapi'));
$PAGE->navbar->add(get_string('pluginname', 'local_certificateapi'));
$PAGE->navbar->add(get_string('edithost', 'local_certificateapi'));

/// Initialize variables.
$hostid = optional_param('hostid', 0, PARAM_INT);

if (!extension_loaded('openssl')) {
    print_error('requiresopenssl', 'mnet');
}

if (!function_exists('xmlrpc_encode_request')) {
    print_error('xmlrpc-missing', 'mnet');
}


$reviewform = new certificateapi_review_host_form(null, array('peer' => $peer)); // the second step (also the edit host form)

echo $OUTPUT->header();

//Form processing and displaying is done here
if ($reviewform->is_cancelled()) {
    $returnpage = new moodle_url('/local/certificateapi/index.php');
    redirect($returnpage);
} elseif ($data = $reviewform->get_data()) {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
        if(isset($data->public_key)) {
            $peer->set_clientid($data->clientid);
            $peer->set_wwwroot($data->hostname);
            $peer->set_fullname($data->fullname);
            $peer->set_key($data->public_key);
            $peer->set_validfrom($data->startdate);
            $peer->set_validto($data->enddate);
            
            $peer->commit();
            
            $strhostupdate = get_string("hostupdate", "local_certificateapi", $peer->fullname);
            
            echo "$strhostupdate<br /><br />";
            echo $OUTPUT->continue_button("index.php");
            
            
        }
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    //Set default data (if any)
    //$reviewform->set_data($toform);
    
    //displays the form
    echo $OUTPUT->box_start();
    echo $OUTPUT->heading(get_string('edithost', 'local_certificateapi'), 3);
    $reviewform->display();
    echo $OUTPUT->box_end();
    
}

// done
echo $OUTPUT->footer();
