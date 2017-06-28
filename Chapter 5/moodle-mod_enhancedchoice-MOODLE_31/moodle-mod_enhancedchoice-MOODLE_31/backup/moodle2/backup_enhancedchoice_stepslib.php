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
 * @package mod_enhancedchoice
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}, Ian David Wild {@link http://heavy-horse.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_enhancedchoice_activity_task
 */

/**
 * Define the complete enhancedchoice structure for backup, with file and id annotations
 */
class backup_enhancedchoice_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $enhancedchoice = new backup_nested_element('enhancedchoice', array('id'), array(
            'name', 'intro', 'introformat', 'publish',
            'showresults', 'display', 'allowupdate', 'showunanswered',
            'limitanswers', 'timeopen', 'timeclose', 'timemodified',
            'completionsubmit'));

        $options = new backup_nested_element('options');

        $option = new backup_nested_element('option', array('id'), array(
            'text', 'textformat', 'maxanswers', 'timemodified'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id'), array(
            'userid', 'optionid', 'timemodified'));

        // Build the tree
        $enhancedchoice->add_child($options);
        $options->add_child($option);

        $enhancedchoice->add_child($answers);
        $answers->add_child($answer);

        // Define sources
        $enhancedchoice->set_source_table('enhancedchoice', array('id' => backup::VAR_ACTIVITYID));

        $option->set_source_sql('
            SELECT *
            FROM {enhancedchoice_options}
            WHERE choiceid = ?
            ORDER BY id',
            array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $answer->set_source_table('enhancedchoice_answers', array('choiceid' => '../../id'));
        }

        // Define id annotations
        $answer->annotate_ids('user', 'userid');

        // Define file annotations
        $enhancedchoice->annotate_files('mod_enhancedchoice', 'intro', null); // This file area hasn't itemid

        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_activity_structure($enhancedchoice);
    }
}
