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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_wavefront_activity_task
 */

/**
 * Define the complete wavefront structure for backup, with file and id annotations
 */
class backup_wavefront_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $wavefront = new backup_nested_element('wavefront', array('id'), array(
            'course', 'folder', 'name', 'comments',
            'timemodified', 'ispublic',
            'intro', 'introformat'
        ));

        $comments = new backup_nested_element('usercomments');
        $comment = new backup_nested_element('comment', array('id'), array(
            'wavefrontid', 'userid', 'commenttext', 'timemodified'
        ));
        
        // currenty one model per wavefront
        $model = new backup_nested_element('model', array('id'), array(
                'wavefrontid', 'description', 'descriptionformat', 'descriptionpos', 
                'stagewidth', 'stageheight', 'camerax', 'cameray', 'cameraz', 'cameraangle', 'camerafar',
                'model', 'timemodified'
        ));

        // Build the tree.

        $wavefront->add_child($model);
        $wavefront->add_child($comments);
        $comments->add_child($comment);
        
        // Define sources.
        $wavefront->set_source_table('wavefront', array('id' => backup::VAR_ACTIVITYID));
        $model->set_source_table('wavefront_model', array('wavefrontid' => backup::VAR_ACTIVITYID));
        
        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $comment->set_source_table('wavefront_comments', array('wavefrontid' => backup::VAR_PARENTID));
        }

        // Define file annotations.
        $wavefront->annotate_files('mod_wavefront', 'model', null);
        $wavefront->annotate_files('mod_wavefront', 'intro', null);

        $comment->annotate_ids('user', 'userid');

        // Return the root element (wavefront), wrapped into standard activity structure.
        return $this->prepare_activity_structure($wavefront);
    }
}
