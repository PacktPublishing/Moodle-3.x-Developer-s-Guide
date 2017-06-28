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
 * Page to allow the administrator to create a new x509 key pair
 *
 * @package    certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once($CFG->dirroot . '/local/certificateapi/peer.php');
require_once($CFG->dirroot . '/local/certificateapi/locallib.php');
require_once($CFG->dirroot . '/local/certificateapi/key_pair_form.php');

require_login();

// Initialize variables.
$clientid = urldecode(optional_param('clientid', '', PARAM_TEXT));
$hostname = urldecode(optional_param('hostname', '', PARAM_TEXT));
$fullname = urldecode(optional_param('fullname', '', PARAM_TEXT));

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

$PAGE->set_context($context);
$PAGE->set_url('/local/certificateapi/key_pair.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'local_certificateapi'));
$PAGE->navbar->add(get_string('pluginname', 'local_certificateapi'));
$PAGE->navbar->add(get_string('createkey', 'local_certificateapi'));

if (!extension_loaded('openssl')) {
    print_error('requiresopenssl', 'mnet');
}

if (!function_exists('xmlrpc_encode_request')) {
    print_error('xmlrpc-missing', 'mnet');
}

$peer = new certificateapi_peer();
$peer->set_clientid($clientid);
$peer->set_wwwroot($hostname);
$peer->set_fullname($fullname);

$keyform = new create_key_pair_form(null, array('peer' => $peer)); // the second step (also the edit host form)

echo $OUTPUT->header();

//Form processing and displaying is done here
if ($keyform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($data = $keyform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    if(!is_null($data)) {
        $dn = (array)$data;
        
        $key_pair = certificateapi_generate_keypair($dn);
        
        echo $OUTPUT->box_start();
        echo $OUTPUT->heading(get_string('connectionkeys', 'local_certificateapi'), 3);
        
        // Display the public and private keys in text boxes
        // Put the private key outside of the form so that it isn't submitted.
        echo('<p>' . get_string('privatekey_desc', 'local_certificateapi') . '</p>');
        echo('<div align="center">');
        echo('<textarea cols="100" rows="17" name="private_key">');
        echo($key_pair['keypair_PEM']);
        echo('</textarea>');
        
        echo('<form name="keyform" action="edit_peer.php" method="POST">');
        echo('</div>');
        echo('<p>' . get_string('publickey_desc', 'local_certificateapi') . '</p>');
        echo('<div align="center">');
        echo('<textarea cols="100" rows="17" name="public_key">');
        echo($key_pair['certificate']);
        echo('</textarea>');
        echo('</div>');
        echo('<input type="hidden" name="clientid" value="' . $data->organizationName . '">');
        echo('<input type="hidden" name="fullname" value="' . $data->fullname . '">');
        echo('<input type="hidden" name="hostname" value="' . $data->commonName . '">');
        echo('<input type="hidden" name="validfrom" value="' . $data->validFrom_time_t . '">' );
        echo('<input type="hidden" name="validto" value="' . $data->validTo_time_t . '">');
        echo('<input type="submit" value="Done">');
        echo('</form>');
        
        echo $OUTPUT->box_end();
    }
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    //Set default data (if any)
    //$reviewform->set_data($toform);
    
    //displays the form
    echo $OUTPUT->box_start();
    echo $OUTPUT->heading(get_string('createkey', 'local_certificateapi'), 3);
    $keyform->display();
    echo $OUTPUT->box_end();
    
}

echo $OUTPUT->footer();