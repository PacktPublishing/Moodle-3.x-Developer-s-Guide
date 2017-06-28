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
 * The main Wavefront 3D model configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_lightboxgallery
 * @copyright 2011 John Kelsh <john.kelsh@netspot.com.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_wavefront_mod_form extends moodleform_mod {

    public function definition() {

        global $CFG;

        $mform =& $this->_form;

        // General options.

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '48', 'maxlength' => '255'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();
        
        // Advanced options.

        $mform->addElement('header', 'wavefrontoptions', get_string('advanced'));

        $yesno = array(0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'comments', get_string('allowcomments', 'wavefront'), $yesno);
        $mform->setType('comments', PARAM_INT);

        $mform->addElement('select', 'ispublic', get_string('makepublic', 'wavefront'), $yesno);
        $mform->setType('ispublic', PARAM_INT);

        // Module options.
        $features = array('groups' => false, 'groupings' => false, 'groupmembersonly' => false,
                          'outcomes' => false, 'gradecat' => false, 'idnumber' => false);

        $this->standard_coursemodule_elements($features);

        $this->add_action_buttons();
    }

}

