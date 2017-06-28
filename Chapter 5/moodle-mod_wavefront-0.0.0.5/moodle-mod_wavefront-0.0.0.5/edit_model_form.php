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
 * @package   mod_wavefront
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/lib/formslib.php');

class mod_wavefront_model_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $model              = $this->_customdata['model'];
        $cm                 = $this->_customdata['cm'];
        $descriptionoptions = $this->_customdata['descriptionoptions'];
        $modeloptions       = $this->_customdata['modeloptions'];

        $context  = context_module::instance($cm->id);
        // Prepare format_string/text options
        $fmtoptions = array(
            'context' => $context);

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('editor', 'description_editor', get_string('modeldescription', 'wavefront'), null, $descriptionoptions);
        $mform->setType('description_editor', PARAM_RAW);
        $mform->addRule('description_editor', get_string('required'), 'required', null, 'client');

        $descriptionposopts = array(
                '0' => get_string('position_bottom', 'wavefront'),
                '1' => get_string('position_top', 'wavefront'),
                '2' => get_string('hide'),
        );
        $mform->addElement('select', 'descriptionpos', get_string('descriptionpos', 'wavefront'), $descriptionposopts);  
        
        $mform->addElement('filemanager', 'model_filemanager', get_string('modelfiles', 'wavefront'), null, $modeloptions);
        $mform->addHelpButton('model_filemanager', 'modelfiles', 'wavefront');
        
        // Stage
        $mform->addElement('header', 'stageoptions', get_string('stageheading', 'wavefront'));
        
        $mform->addElement('text', 'stagewidth', get_string('stagewidth', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('stagewidth', 400);
        $mform->setType('stagewidth', PARAM_INT);
        
        $mform->addElement('text', 'stageheight', get_string('stageheight', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('stageheight', 400);
        $mform->setType('stageheight', PARAM_INT);
        
        // Camera
        $mform->addElement('header', 'cameraoptions', get_string('cameraheading', 'wavefront'));
        
        $mform->addElement('text', 'cameraangle', get_string('cameraangle', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('cameraangle', 45);
        $mform->setType('cameraangle', PARAM_INT);
        
        $mform->addElement('text', 'camerafar', get_string('camerafar', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('camerafar', 1000);
        $mform->setType('camerafar', PARAM_INT);
        
        $mform->addElement('text', 'camerax', get_string('camerax', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('camerax', 0);
        $mform->setType('camerax', PARAM_INT);
        
        $mform->addElement('text', 'cameray', get_string('cameray', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('cameray', 1);
        $mform->setType('cameray', PARAM_INT);
        
        $mform->addElement('text', 'cameraz', get_string('cameraz', 'wavefront'), 'maxlength="5" size="5"');
        $mform->setDefault('cameraz', 200);
        $mform->setType('cameraz', PARAM_INT);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

//-------------------------------------------------------------------------------
        $this->add_action_buttons();

//-------------------------------------------------------------------------------
        $this->set_data($model);
    }

    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);

        return $errors;
    }
}

