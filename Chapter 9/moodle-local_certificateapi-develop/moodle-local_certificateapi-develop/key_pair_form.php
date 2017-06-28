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

require_once($CFG->libdir . '/formslib.php');

class create_key_pair_form extends moodleform {
    function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $peer = $this->_customdata['peer'];
        
        $mform->addElement('text', 'fullname', get_string('fullname', 'local_certificateapi'));
        $mform->addRule('fullname', null, 'required', null, 'countryName');
        $mform->setType('fullname', PARAM_TEXT);
        
        $mform->addElement('text', 'countryName', get_string('countryName', 'local_certificateapi'), array('maxlength'=>'2'));
        $mform->addRule('countryName', null, 'required', null, 'countryName');
        $mform->setType('countryName', PARAM_TEXT);
        
        $mform->addElement('text', 'stateOrProvinceName', get_string('stateOrProvinceName', 'local_certificateapi'), array('maxlength'=>'128'));
        $mform->addRule('stateOrProvinceName', null, 'required', null, 'stateOrProvinceName');
        $mform->setType('stateOrProvinceName', PARAM_TEXT);
        
        $mform->addElement('text', 'localityName', get_string('localityName', 'local_certificateapi'), array('maxlength'=>'128'));
        $mform->addRule('localityName', null, 'required', null, 'localityName');
        $mform->setType('localityName', PARAM_TEXT);
        
        $mform->addElement('text', 'organizationName', get_string('organizationName', 'local_certificateapi'), array('maxlength'=>'64'));
        $mform->addRule('organizationName', null, 'required', null, 'organizationName');
        $mform->setType('organizationName', PARAM_TEXT);
        
        $mform->addElement('text', 'organizationalUnitName', get_string('organizationalUnitName', 'local_certificateapi'), array('maxlength'=>'64'));
        $mform->addRule('organizationalUnitName', null, 'required', null, 'organizationalUnitName');
        $mform->setType('organizationalUnitName', PARAM_TEXT);
        
        $mform->addElement('text', 'commonName', get_string('commonName', 'local_certificateapi'), array('maxlength'=>'64'));
        $mform->addRule('commonName', null, 'required', null, 'commonName');
        $mform->setType('commonName', PARAM_TEXT);
        
        $mform->addElement('text', 'subjectAltName', get_string('subjectAltName', 'local_certificateapi'));
        $mform->addRule('subjectAltName', null, 'required', null, 'subjectAltName');
        $mform->setType('subjectAltName', PARAM_TEXT);
        
        $mform->addElement('text', 'emailAddress', get_string('emailAddress', 'local_certificateapi'), array('maxlength'=>'64'));
        $mform->addRule('emailAddress', null, 'required', null, 'emailAddress');
        $mform->setType('emailAddress', PARAM_EMAIL);
        
        $mform->addElement('static', 'keytimewindow', '', get_string('keytimewindow', 'local_certificateapi'));
        $mform->addElement('date_selector', 'validFrom_time_t', get_string('startdate', 'local_certificateapi'), array('optional'=>true, 'stopyear'=>2030));
        
        $mform->addElement('date_selector', 'validTo_time_t', get_string('enddate', 'local_certificateapi'), array('optional'=>true, 'stopyear'=>2030));
        
        // finished with static stuff, print save button
        $this->add_action_buttons(false);
    }
    
    function definition_after_data() {
         
        $data = new stdClass();
    
        $peer = $this->_customdata['peer'];
    
        $data->fullname = $peer->fullname;
        $data->commonName = $peer->wwwroot;   
        $data->subjectAltName = $peer->wwwroot;
        $data->organizationName = $peer->clientid;
        
        $this->set_data($data);
    }
}