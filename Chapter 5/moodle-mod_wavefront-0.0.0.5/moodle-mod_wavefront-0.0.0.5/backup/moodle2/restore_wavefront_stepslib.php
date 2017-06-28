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
 * Define all the restore steps that will be used by the restore_wavefront_activity_task
 */

/**
 * Structure step to restore one wavefront activity
 */
class restore_wavefront_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $wavefront = new restore_path_element('wavefront', '/activity/wavefront');
        $paths[] = $wavefront;

        $model = new restore_path_element('wavefront_model', '/activity/wavefront/model');
        $paths[] = $model;

        if ($userinfo) {
            $comment = new restore_path_element('wavefront_comment', '/activity/wavefront/usercomments/comment');
            $paths[] = $comment;
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_wavefront($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        // Insert the wavefront record.
        $newitemid = $DB->insert_record('wavefront', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_wavefront_comment($data) {
        global $DB;

        $data = (object)$data;

        $data->wavefront = $this->get_new_parentid('wavefront');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (isset($data->comment)) {
            $data->commenttext = $data->comment;
        }
        $DB->insert_record('wavefront_comments', $data);
    }

    protected function process_wavefront_model($data) {
        global $DB;

        $data = (object)$data;

        $data->wavefrontid = $this->get_new_parentid('wavefront');
        // TODO: model var to match model.
        $DB->insert_record('wavefront_model', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_wavefront', 'model', null);
        $this->add_related_files('mod_wavefront', 'intro', null);
    }
}
