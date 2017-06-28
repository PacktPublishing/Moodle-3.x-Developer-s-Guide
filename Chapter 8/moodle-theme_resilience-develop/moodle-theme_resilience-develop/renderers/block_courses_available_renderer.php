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

include_once($CFG->dirroot . "/blocks/courses_available/renderer.php");

class theme_resilience_block_courses_available_renderer extends block_courses_available_renderer {
    
    public function get_course_title($title) {
        return parent::get_course_title($title);
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
            
            $url = new moodle_url($CFG->wwwroot.'/blocks/courses_available/overview.php?id='.$course->id);
            $linkString = get_string('description', 'block_courses_available');
            $html = html_writer::link($url, $linkString, array('class' => 'button course_overview'));
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
            $linkString = get_string('retakecourse', 'block_courses_available');
        } elseif ($progress == 0) {
            $linkString = get_string('startcourse', 'block_courses_available');
        } else {
            $linkString = get_string('continuecourse', 'block_courses_available');
        }
        
        $html = html_writer::link($url, $linkString, array('class' => 'button course_link'));
        
        return $html;
    }
    
    public function get_overview($course) {
        return parent::get_overview($course);
    }
}