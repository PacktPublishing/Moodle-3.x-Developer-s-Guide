<?php
// This file is part of the Local welcome plugin
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
* certificateapi package
*
* @package    local
* @subpackage certificateapi
* @copyright  2017 Ian Wild
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once '../../config.php';
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/certificateapi/locallib.php');
require_once($CFG->dirroot . '/local/certificateapi/peer.php');

/**
 * The very basic first step add new host form - just wwwroot & application
 * The second form is loaded up with the information from this one.
 */
class certificateapi_simple_host_form extends moodleform {
    function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id', -1);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('text', 'fullname', get_string('fullname', 'local_certificateapi'));
        $mform->addHelpButton('fullname', 'fullname', 'local_certificateapi');
        $mform->addRule('fullname', null, 'required', null, 'client');
        $mform->setType('fullname', PARAM_TEXT);
        
        $mform->addElement('text', 'hostname', get_string('hostname', 'local_certificateapi'));
        $mform->addHelpButton('hostname', 'hostname', 'local_certificateapi');
        $mform->addRule('hostname', null, 'required', null, 'client');
        $mform->setType('hostname', PARAM_TEXT);

        $mform->addElement('text', 'clientid', get_string('clientid', 'local_certificateapi'));
        $mform->addHelpButton('clientid', 'clientid', 'local_certificateapi');
        $mform->addRule('clientid', null, 'required', null, 'client');
        $mform->setType('clientid', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('addhost', 'local_certificateapi'));
    }

    function validation($data, $files = array()) {
        global $DB;

        $clientid = $data['clientid'];
        
        if ($host = $DB->get_record('certificateapi_host', array('clientid' => $clientid))) {
            $data['id'] = $host->id;
        }
        return array();
    }
}

$context = context_system::instance();

require_login();
if (!is_siteadmin()) {
    return '';
}
$PAGE->set_context($context);
$PAGE->set_url('/local/certificateapi/index.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_certificateapi'));
$PAGE->navbar->add(get_string('pluginname', 'local_certificateapi'));

/// Initialize variables.
$hostid = optional_param('hostid', 0, PARAM_INT);

if (!extension_loaded('openssl')) {
    print_error('requiresopenssl', 'mnet');
}

if (!function_exists('xmlrpc_encode_request')) {
    print_error('xmlrpc-missing', 'mnet');
}

$peer = new certificateapi_peer();
$simpleform = new certificateapi_simple_host_form(new moodle_url('/local/certificateapi/edit_peer.php')); // the one that goes on the bottom of the main page

// normal flow - just display all hosts with links
echo $OUTPUT->header();
$hosts = certificateapi_get_hosts();

// print the list of all hosts, with little action links and buttons
$table = new html_table();
$table->head = array(
        get_string('fullname', 'local_certificateapi'),
        get_string('hostname', 'local_certificateapi'),
        get_string('clientid', 'local_certificateapi'),
        get_string('startdate', 'local_certificateapi'),
        get_string('enddate', 'local_certificateapi'),
        get_string('last_connect_time', 'local_certificateapi'),
        ''
);
$table->wrap = array('', '', '', '', '', ''); // if we wrap this then it leaks off the right of the page.
$baseurl = new moodle_url('/local/certificateapi/edit_peer.php');
foreach($hosts as $host) {
    $hosturl = new moodle_url($baseurl, array('id' => $host->id));
    // process all hosts first since it's the easiest
    
    if ($host->last_connect_time == 0) {
        $last_connect = get_string('never');
    } else {
        $last_connect = date('H:i:s d/m/Y', $host->last_connect_time);
    }
    $table->data[] = array(
            html_writer::link($hosturl, $host->fullname),
            html_writer::link($hosturl, $host->wwwroot),
            html_writer::link($hosturl, $host->clientid),
            html_writer::link($hosturl, date('d/m/Y', $host->public_key_validfrom)),
            html_writer::link($hosturl, date('d/m/Y', $host->public_key_expires)),
            $last_connect,
            $OUTPUT->single_button(new moodle_url('/local/certificateapi/delete_peer.php', array('id' => $host->id)), get_string('delete'))
    );
}
echo html_writer::table($table);

// finally, print the initial form to add a new host
echo $OUTPUT->box_start();
echo $OUTPUT->heading(get_string('addnewhost', 'local_certificateapi'), 3);
$simpleform->display();
echo $OUTPUT->box_end();

// done
echo $OUTPUT->footer();
