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
 * Courses available block definition
 *
 * @package    block_courses_available
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/courses_available/lib.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Completion Progress block class
 *
 * @copyright 2016 Michael de Raadt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_courses_available extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('config_default_title', 'block_courses_available');
    }

    /**
     *  we have global config/settings data
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        if (isset($this->config->progressTitle) && trim($this->config->progressTitle) != '') {
            $this->title = format_string($this->config->progressTitle);
        }
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => true,
            'mod'            => false,
            'my'             => true
        );
    }

    /**
     * Creates the blocks main content
     *
     * @return string
     */
    public function get_content() {
        global $USER, $COURSE, $CFG, $OUTPUT, $DB;

        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $blockinstancesonpage = array();

        // Guests do not have any progress. Don't show them the block.
        if (!isloggedin() or isguestuser()) {
            return $this->content;
        }

        // Draw the multi-bar content for the Dashboard and Front page.
        if (!$CFG->enablecompletion) {
            $this->content->text .= get_string('completion_not_enabled', 'block_courses_available');
            return $this->content;
        }

        // Show a message when the user is not enrolled in any courses.
        $courses = enrol_get_my_courses();
        if (($this->page->user_is_editing() || is_siteadmin()) && empty($courses)) {
            $this->content->text = get_string('nocourses', 'block_courses_available');
            return $this->content;
        }
            
        $courseinstances = array();
        
        foreach ($courses as $courseid => $course) {
            // Get specific course completion data
            $completion = new completion_info($course);
            if ($course->visible && $completion->is_enabled()) {
                $context = CONTEXT_COURSE::instance($course->id);
                $params = array('contextid' => $context->id, 'pagetype' => 'course-view-%');
                
                $courseinstance = new stdClass();
                
                $courseinstance->course = $course;
                $courseinstance->activities = block_courses_available_get_activities($course->id);
                $courseinstance->activities = block_courses_available_filter_visibility($courseinstance->activities,
                                                     $USER->id, $course->id);
                
                $courseinstances[] = $courseinstance;
            }
        }
        
        $renderer = $this->page->get_renderer('block_courses_available');
                  
        if (!empty($courseinstances)) {
            $toStartTable = array();
            $inProgressTable = array();
            $completedTable = array();
            
            
            $table = new html_table();
            $table->attributes = array('class'=>'availablecoursestable');
            $table->align = array ('left', 'left', 'right', 'right');
            
            foreach ($courseinstances as $courseinstance) {
                $course = get_course($courseinstance->course->id);
                
                $row = array();
                
                $row[] = $renderer->get_course_title($course->fullname);
                
                $row[] = $renderer->get_summary($course);
                
                $submissions = block_courses_available_student_submissions($course->id, $USER->id);
                $completions = block_courses_available_completions($courseinstance->activities, $USER->id, $course, $submissions);
                
                $json = block_courses_available_json(
                        $courseinstance->activities,
                        $completions,
                        $this->config,
                        $USER->id,
                        $courseid,
                        $this->instance->id);
                
                $completion_data = json_decode($json);
                
                $row[] = $renderer->get_course_link($course, $completion_data);
                
                $progress = intval($completion_data->percentage);
                
                if ($progress == 100) {
                    $completedTable[] = $row;
                } elseif ($progress == 0) {
                    $toStartTable[] = $row;
                } else {
                    $inProgressTable[] = $row;
                }
             
            }// foreach
            
            $table->data = array_merge($inProgressTable, $toStartTable, $completedTable);
            $this->content->text = html_writer::table($table);
        }
        
        // Show a message explaining lack of buttons, but only while editing is on.
        if ($this->page->user_is_editing() && $this->content->text == '') {
            $this->content->text = get_string('nocourses', 'block_courses_available');
        }

        return $this->content;
    }
}
