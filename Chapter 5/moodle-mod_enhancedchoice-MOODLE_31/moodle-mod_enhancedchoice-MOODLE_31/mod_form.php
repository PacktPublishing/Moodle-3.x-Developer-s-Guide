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
 * @package    mod_enhancedchoice
 * @copyright  2017 Ian David Wild {@link http://heavy-horse.co.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_enhancedchoice_mod_form extends moodleform_mod {
	
    function definition() {
        global $CFG, $ENHANCEDCHOICE_SHOWRESULTS, $ENHANCEDCHOICE_PUBLISH, $ENHANCEDCHOICE_DISPLAY, $DB;

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('choicename', 'enhancedchoice'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description', 'enhancedchoice'));

//-------------------------------------------------------------------------------
        $repeatarray = array();
        $repeatarray[] = $mform->createElement('header', '', get_string('option','enhancedchoice').' {no}');
        $repeatarray[] = $mform->createElement('editor', 'option', get_string('option','enhancedchoice'), null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        $repeatarray[] = $mform->createElement('text', 'limit', get_string('limit','enhancedchoice'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        $menuoptions = array();
        $menuoptions[0] = get_string('disable');
        $menuoptions[1] = get_string('enable');
        $mform->addElement('header', 'timerestricthdr', get_string('limit', 'enhancedchoice'));
        $mform->addElement('select', 'limitanswers', get_string('limitanswers', 'enhancedchoice'), $menuoptions);
        $mform->addHelpButton('limitanswers', 'limitanswers', 'enhancedchoice');

        if ($this->_instance){
            $repeatno = $DB->count_records('enhancedchoice_options', array('choiceid'=>$this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['limit']['default'] = 0;
        $repeateloptions['limit']['disabledif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['limit']['rule'] = 'numeric';
        $repeateloptions['limit']['type'] = PARAM_INT;
        $repeateloptions['option']['helpbutton'] = array('enhancedchoice_options', 'enhancedchoice');
        
        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3);

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'timerestricthdr', get_string('timerestrict', 'enhancedchoice'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'enhancedchoice'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("choiceopen", "enhancedchoice"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("choiceclose", "enhancedchoice"));
        $mform->disabledIf('timeclose', 'timerestrict');

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));

        $mform->addElement('select', 'display', get_string("displaymode","enhancedchoice"), $ENHANCEDCHOICE_DISPLAY);

        $mform->addElement('select', 'showresults', get_string("publish", "enhancedchoice"), $ENHANCEDCHOICE_SHOWRESULTS);

        $mform->addElement('select', 'publish', get_string("privacy", "enhancedchoice"), $ENHANCEDCHOICE_PUBLISH);
        $mform->disabledIf('publish', 'showresults', 'eq', 0);

        $mform->addElement('selectyesno', 'allowupdate', get_string("allowupdate", "enhancedchoice"));

        $mform->addElement('selectyesno', 'showunanswered', get_string("showunanswered", "enhancedchoice"));


//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
        global $DB;
        
        if (!empty($this->_instance) && ($options = $DB->get_records('enhancedchoice_options',array('choiceid'=>$this->_instance), 'id', 'id,text,textformat'))
               && ($options2 = $DB->get_records_menu('enhancedchoice_options', array('choiceid'=>$this->_instance), 'id', 'id,maxanswers')) ) {
            $choiceids=array_keys($options);
            $options=array_values($options);
            $options2=array_values($options2);

            $editoroptions = enhancedchoice_get_editor_options();
            
            $idx = 0; 
            foreach (array_keys($options) as $key){
            	
            	$draftid = file_get_submitted_draft_itemid('option['.$key.']');
            	
            	$defaulttext = file_prepare_draft_area($draftid, 
            			$this->context->id,
            			'mod_enhancedchoice', 
            			'option', 
            			!empty($choiceids[$key]) ? (int) $choiceids[$key] : null, // Itemid.,
            			$editoroptions,
            			$options[$key]->text);
            	$default_values['option['.$key.']']['text'] = $defaulttext;
            	$default_values['option['.$key.']']['itemid'] = $draftid;
            	
                $default_values['limit['.$key.']'] = $options2[$key];
                $default_values['optionid['.$key.']'] = $choiceids[$key];
                
                $idx++;
            }
        }
        
        if (empty($default_values['timeopen'])) {
            $default_values['timerestrict'] = 0;
        } else {
            $default_values['timerestrict'] = 1;
        }

    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $choices = 0;
        foreach ($data['option'] as $option){
            if ($option['text'] != ''){
                $choices++;
            }
        }

        if ($choices < 1) {
           $errors['option[0]'] = get_string('atleastoneoption', 'enhancedchoice');
        }

        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Set up completion section even if checkbox is not ticked
        if (empty($data->completionsection)) {
            $data->completionsection=0;
        }
        return $data;
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'enhancedchoice'));
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}

