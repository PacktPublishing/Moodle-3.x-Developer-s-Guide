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

require_once($CFG->libdir . '/formslib.php');

class certificateapi_review_host_form extends moodleform {
    function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $peer = $this->_customdata['peer'];

        //$mform->addElement('hidden', 'oldpublickey');
        $mform->addElement('hidden', 'last_connect_time');
        $mform->setType('last_connect_time', PARAM_INT);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('text', 'fullname', get_string('fullname', 'local_certificateapi'));
        $mform->addHelpButton('fullname', 'fullname', 'local_certificateapi');
        $mform->addRule('fullname', null, 'required', null, 'fullname');
        $mform->setType('fullname', PARAM_TEXT);
        
        $mform->addElement('text', 'hostname', get_string('hostname', 'local_certificateapi'));
        $mform->addHelpButton('hostname', 'hostname', 'local_certificateapi');
        $mform->addRule('hostname', null, 'required', null, 'hostname');
        $mform->setType('hostname', PARAM_TEXT);
        
        $mform->addElement('text', 'clientid', get_string('clientid', 'local_certificateapi'));
        $mform->addHelpButton('clientid', 'clientid', 'local_certificateapi');
        $mform->addRule('clientid', null, 'required', null, 'clientid');
        $mform->setDefault('clientid', $peer->clientid);
        $mform->setType('clientid', PARAM_TEXT);
        
        $key_creation = new moodle_url('/local/certificateapi/key_pair.php');
        $keycreationlink = $key_creation->out() . '?clientid=' . urlencode($peer->clientid) . '&hostname=' . urlencode($peer->wwwroot) . '&fullname=' . urlencode($peer->fullname);
        $description = get_string('peer_config_desc', 'local_certificateapi', $keycreationlink); 
        $mform->addElement('static', 'description', '', $description);
        $mform->addElement('textarea', 'public_key', get_string('publickey', 'local_certificateapi'), array('rows' => 17, 'cols' => 100, 'class' => 'smalltext'));
        $mform->addHelpButton('public_key', 'publickey', 'local_certificateapi');
        $mform->addRule('public_key', null, 'required', null, 'public_key');
        $mform->setType('public_key', PARAM_TEXT);
        
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_certificateapi'), array('stopyear'=>2030));
        $mform->addRule('startdate', null, 'required', null, 'startdate');
        
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_certificateapi'), array('stopyear'=>2030));
        $mform->addRule('enddate', null, 'required', null, 'enddate');
        
        // finished with form controls, now the static informational stuff
        if ($peer) {
            $lastconnect = '';
            if ($peer->last_connect_time == 0) {
                $lastconnect = get_string('never', 'local_certificateapi');
            } else {
                $lastconnect = date('H:i:s d/m/Y', $peer->last_connect_time);
            }

            $mform->addElement('static', 'lastconnect', get_string('last_connect_time', 'local_certificateapi'), $lastconnect);
            
            $credstr = '';
            if ($credentials = $peer->check_credentials($peer->public_key)) {
                foreach($credentials['subject'] as $key => $credential) {
                    if (is_scalar($credential)) {
                        $credstr .= str_pad($key, 16, " ", STR_PAD_LEFT).': '.$credential."\n";
                    } else {
                        $credstr .= str_pad($key, 16, " ", STR_PAD_LEFT).': '.var_export($credential,1)."\n";
                    }
                }
            }

            $mform->addElement('static', 'certdetails', get_string('certdetails', 'local_certificateapi'), $OUTPUT->box('<pre>' . $credstr . '</pre>'));
        }

        // finished with static stuff, print save button
        $this->add_action_buttons(true);
    }
    
    function definition_after_data() {
   
        $data = new stdClass();
        
        $peer = $this->_customdata['peer'];
        
        $data->fullname = $peer->fullname;
        $data->hostname = $peer->wwwroot;
        $data->clientid = $peer->clientid;
        
        $data->last_connect_time = $peer->last_connect_time;
        $data->id = $peer->id;
        
        if($peer->public_key_validfrom > 0) {
        	$data->startdate = $peer->public_key_validfrom;
        }
        
        if($peer->public_key_expires > 0) {
        	$data->enddate = $peer->public_key_expires;
        }
        
        if($peer->public_key != '') {
        	$data->public_key = $peer->public_key;
        }
        
        $this->set_data($data);
    }

    function validation($data, $files = array()) {
        $errors = array();
        //if ($data['oldpublickey'] == $data['public_key']) {
        //    return;
        //}
        $peer = new certificateapi_peer();
        $peer->wwwroot = $data['hostname']; // just hard-set this rather than bootstrap the object
        if (!$credentials = $peer->check_credentials($data['public_key'])) {
            $errmsg = '';
            foreach ($peer->error as $err) {
                $errmsg .= $err['code'] . ': ' . $err['text'].'<br />';
            }
            $errors['public_key'] = get_string('invalidpubkey', 'local_certificateapi', $errmsg);
        }
        unset($peer);
        return $errors;
    }
}