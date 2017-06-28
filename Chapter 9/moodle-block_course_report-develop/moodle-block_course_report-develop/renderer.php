<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Block course report renderer.
 * @package   block_course_report
 * @copyright 2017 Moodle Pty Ltd (http://moodle.com)
 * @author    Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_report_renderer extends plugin_renderer_base {

    
    /**
     * Get link to bubble chart report (HTML)
     * @param moodle_url $url the report page to be redirected to
     * @return string html
     */
    public function get_report_link($courseid) {
    	global $CFG;
    	
    	$url = new moodle_url($CFG->wwwroot.'/blocks/course_report/viewreport.php', array('id'=>$courseid));
    	
    	$linkString = get_string('bubblechart', 'block_course_report');
    	
    	$html = html_writer::link($url, $linkString, array('class' => 'button report_link'));
        
        return $html;
    }

    /**
     * Returns the HTML for a button that links to the block_courses_available overview page
     *
     * @param unknown $course
     * @return string
     */
    public function get_report_exit_btn($courseid) {    	
    	global $CFG;
    		
    	$link = new moodle_url($CFG->wwwroot.'/course/view.php?id='.$courseid);
    	$buttonString = get_string('returntocourse', 'block_course_report');
    	$button = new single_button($link, $buttonString, 'get');
    	$button->class = 'tablebutton';
    		
    	$html = $this->output->render($button);
    	
    	return $html;
    }
}
