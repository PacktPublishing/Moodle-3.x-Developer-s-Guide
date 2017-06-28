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
 * courses_available block rendrer
 *
 * @package    block_courses_available
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/courses_available/lib.php');

/**
 * Courses_available block renderer
 *
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_courses_available_renderer extends plugin_renderer_base {
    
    /**
     * Returns the HTML for the course title. For now just return the title as-is.
     * 
     * @param unknown $title
     * @return unknown
     */
    public function get_course_title($title) {
        return $title;
    }

    /**
     * Returns the HTML for a button that links to the block_courses_available overview page
     * 
     * @param unknown $course
     * @return string
     */
    public function get_summary($course) {
        
        $html = '-';
        
        if(isset($course->summary)) {
            global $CFG;
            
            $link = new moodle_url($CFG->wwwroot.'/blocks/courses_available/overview.php?id='.$course->id);
            $buttonString = get_string('description', 'block_courses_available');
            $button = new single_button($link, $buttonString, 'get');
            $button->class = 'tablebutton';
            
            $html = $this->output->render($button);
        }
        return $html;
    }
    
    /**
     * Returns the HTML for a button that navigates to the course. The button text reflects the user's completion progress.
     * 
     * @param unknown $course
     * @param unknown $completion_data
     * @return string
     */
    public function get_course_link($course, $completion_data) {
        global $CFG; 
        
        $html = '';
        
        $url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id'=>$course->id));
        
        $progress = intval($completion_data->percentage);
        
        if ($progress == 100) {
            $buttonString = get_string('retakecourse', 'block_courses_available');
        } elseif ($progress == 0) {
            $buttonString = get_string('startcourse', 'block_courses_available');
        } else {
            $buttonString = get_string('continuecourse', 'block_courses_available');
        }
        $button = new single_button($url, $buttonString, 'get');
        $button->class = 'tablebutton';
        
        $html = $this->output->render($button);
        
        return $html;
    }
    
    /**
     * 
     * 
     * @param unknown $course
     * @return string|boolean
     */
    public function get_overview($course) {
        global $CFG, $DB;
        
        $data = new stdClass();
        
        $data->summary = format_text($course->summary, $course->summaryformat, array('overflowdiv'=>true), $course->id);
        
        $data->names = array();
        
        if (!empty($CFG->coursecontact)) {
            $context = context_course::instance($course->id);
            
            $coursecontactroles = explode(',', $CFG->coursecontact);
            foreach ($coursecontactroles as $roleid) {
                $role = $DB->get_record('role', array('id'=>$roleid));
                $roleid = (int) $roleid;
                
                if ($users = get_role_users($roleid, $context, true)) {
                    foreach ($users as $teacher) {
                        $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                        $data->names[] = format_string(role_get_name($role, $context)).': <a href="'.$CFG->wwwroot.'/user/view.php?id='.
                                $teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
                    }
                }
            }
        }
        
        /*
         * button box
         */
        $buttonBox = $this->output->box_start('generalbox icons');
        $cancel = new single_button(new moodle_url($CFG->wwwroot.'/my'), get_string('homepage', 'block_courses_available'), 'get');
        $url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id'=>$course->id));
        $continue = new single_button($url, get_string('coursepage', 'block_courses_available'), 'get');
        
        $attr = array('id'=>'summarybuttons','class' => 'buttons');
        $buttonBox .= html_writer::tag('div', $this->output->render($continue).$this->output->render($cancel), $attr);
        $buttonBox .= $this->output->box_end();
        
        $data->buttons = $buttonBox;
        
        return $this->render_from_template('block_courses_available/course_overview', $data);
    }
}
