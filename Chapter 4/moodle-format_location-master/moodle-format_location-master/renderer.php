<?php
// This file is part of the Kamedia GPS course format for Moodle - http://moodle.org/
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
 * Renderer for outputting the GPS Format free course format.
 *
 * @package format_gps
 * @copyright 2013 Barry Oosthuizen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/renderer.php');
require_once($CFG->dirroot . '/course/format/location/locallib.php');

/**
 * Basic renderer for gps pro format.
 *
 * @copyright 2013 Barry Oosthuizen
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_location_renderer extends format_section_renderer_base {
    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE, $SESSION, $DB, $USER;
		
		$location = $this->location_get_user_location($USER->id);
		
        $updateposition = get_string('updateposition', 'format_location');
        $loadinggps = html_writer::div(get_string('loadinggps', 'format_location'), 'loadinggps');
        echo $loadinggps;
        
        // Module form with map.
        $viewcourse = new moodle_url('/course/view.php', array('id' => $course->id));
        $updatecourseviewlink = html_writer::empty_tag('br') .
        html_writer::link($viewcourse,
                get_string('updatepage', 'format_location'),
                array('class' => 'location-continue'));
        $map = html_writer::div('', 'googlemap', array('id' => 'map'));
        $mapcontainer = html_writer::div($map, 'mapcontainer', array('id' => 'mapcontainer'));
        $modalform = html_writer::div($mapcontainer . $updatecourseviewlink, 'popupgeo', array('id' => 'popupgeo'));
        echo $modalform;
		
		$viewcourse = new moodle_url('/course/view.php', array('id' => $course->id));
        $modaldiv = html_writer::div($updateposition, 'updateposition hide', array('id' => 'updatepositionclick'));
		
		if($location){
			$userlatitude = round($location->latitude, 6);
			$userlongitude = round($location->longitude, 6);
			
			$vars = explode(".",$userlatitude);
			$deg = $vars[0];
			$deg = str_replace("-", "", "$deg");
			$tempma = "0.".$vars[1];
			$tempma = $tempma * 3600;
			$min = floor($tempma / 60);
			$sec = $tempma - ($min*60);
			$sec = round("$sec", 2);
			
			$south = new lang_string('south', 'format_location');
			$north = new lang_string('north', 'format_location');
			if (strpos($userlatitude, '-') !== false) { 
				$latPos = $south;
			} else {
				$latPos = $north;
			}
			$DMS_LAT = "$latPos $deg&deg;$min'$sec\"";

			$vars_ = explode(".",$userlongitude);
			$deg_ = $vars_[0];
			$deg_ = str_replace("-", "", "$deg_");
			$tempma_ = "0.".$vars_[1];
			
			$tempma_ = $tempma_ * 3600;
			$min_ = floor($tempma_ / 60);
			$sec_ = $tempma_ - ($min_*60);
			$sec_ = round("$sec_", 2);
			
			$west = new lang_string('west', 'format_location');
			$east = new lang_string('east', 'format_location');
			if(strpos($userlongitude, '-') !== false) { 
				$latPos = $west;
			} else {
				$latPos = $east;
			}  
			$DMS_Long = "$latPos $deg_&deg;$min_'$sec_\"";
			
			
			echo '<div id="updatepositionclick" class="hide" ><a href="' . $viewcourse . '" class="location-continue" style="float: left;">' . $updateposition . '</a><br class="rwd-break"><br class="rwd-break"><a href="http://maps.google.com/?q=' . $userlatitude . ',' . $userlongitude. '" class="location-continue responsive_button" style="float: left;" target="_blank">Google Maps</a></div>';
			echo '<div style="float: left; margin-top: 3px; line-height: 1.1em" class="responsive_button">' . $DMS_LAT . '<br />' . $DMS_Long . '</div>';
			echo '<div style="clear: both; height: 1px; overflow: hidden"></div>';
		} else {
			echo '<div id="updatepositionclick" class="hide"><a href="' . $viewcourse . '" class="location-continue">' . $updateposition . '</a></div>';
			$nogps = new lang_string('nogps', 'format_location');
			echo '<br /><pre style="text-align: center !important;">' . $nogps . '</pre>';
		}
		
		
        $modinfo = get_fast_modinfo($course);

        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {

            if ($section == 0) {
                // 0-section is displayed a little different then the others.
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    $courserenderer = $PAGE->get_renderer('core', 'course');
                    echo $courserenderer->course_section_cm_list($course, $thissection, 0);
                    if ($PAGE->user_is_editing()) {
                        echo $courserenderer->course_section_add_cm_control($course, 0, 0);
                    }
                    echo $this->section_footer();
                }
                continue;
            }
            if ($section > $course->numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available.
            // but showavailability is turned on (and there is some available info text).
            $proximity = new stdClass();
			
            if ($thissection->format_location_restricted == FORMAT_LOCATION_RESTRICTED) {
                if ($location) {
                    $proximity = format_location_check_proximity($thissection, $location);
                } else {
                    $proximity->status = 'notallowed';
                }
            } else {
                $proximity->status = 'ok';
            }

            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available && $thissection->showavailability
                    && !empty($thissection->availableinfo));

            if (!$showsection || $proximity->status == 'toofar' || $proximity->status == 'notallowed') {
                if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
                    // Do nothing.
                } else {
                    $thissection->visible = false;
                    // Hidden section message is overridden by 'unavailable' control
                    // (showavailability option).
                    if ($proximity->status == 'toofar') {
                        if (!$course->hiddensections && $thissection->available) {
                            echo $this->location_section_hidden($section);
                        }
                    } else if ($proximity->status == 'notallowed') {
                        if (!$course->hiddensections && $thissection->available) {
                            echo $this->location_section_notallowed($section);
                        }
                    } else {
                        if (!$course->hiddensections && $thissection->available) {
                            echo $this->section_hidden($section);
                        }
                    }
                    continue;
                }
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    $courserenderer = $PAGE->get_renderer('core', 'course');
                    echo $courserenderer->course_section_cm_list($course, $thissection, 0);
                    if ($PAGE->user_is_editing()) {
                        echo $courserenderer->course_section_add_cm_control($course, $section, 0);
                    }
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // This is not stealth section or it is empty.
                    continue;
                }
                echo $this->stealth_section_header($section);
                $courserenderer = $PAGE->get_renderer('core', 'course');
                echo $courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();
            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                            array('courseid' => $course->id,
                                'increase' => true,
                                'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon . get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                                array('courseid' => $course->id,
                                    'increase' => false,
                                    'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon . get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }
            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
    }
	
    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        if (!has_capability('moodle/course:update', context_course::instance($course->id))) {
            return array();
        }

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($course->marker == $section->section) { // Show the "light globe" on/off.
            $url->param('marker', 0);
            $controls[] = html_writer::link($url, html_writer::empty_tag('img',
                    array('src' => $this->output->pix_url('i/marked'),
                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                    array('title' => get_string('markedthistopic'),
                        'class' => 'editing_highlight'));
        } else {
            $url->param('marker', $section->section);
            $controls[] = html_writer::link($url, html_writer::empty_tag('img',
                    array('src' => $this->output->pix_url('i/marker'),
                                'class' => 'icon', 'alt' => get_string('markthistopic'))),
                    array('title' => get_string('markthistopic'),
                        'class' => 'editing_highlight'));
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
    }

    /**
     * New bits for the Geo Topics format
     */
    protected function section_left_content($section, $course, $onsectionpage) {
        global $DB, $USER;

        $o = parent::section_left_content($section, $course, $onsectionpage);
        return $o;
    }

    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        $o = parent::section_header($section, $course, $onsectionpage, $sectionreturn);

        return html_writer::tag('div', $o, array('class' => 'section-header'));
    }

    /**
     * Generate the html for a hidden section
     *
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return string HTML to output.
     */
    protected function location_section_hidden($sectionno) {
        $o = '';
        $geo = format_location_icon();
        $o.= html_writer::start_tag('li', array('id' => 'section-' . $sectionno, 'class' => 'section main clearfix hidden'));
        $o.= html_writer::tag('div', '', array('class' => 'left side'));
        $o.= html_writer::tag('div', '', array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
        $o.= $geo;
        $o.= $this->output->spacer(array('width' => '10px'));
        $o.= get_string('gpsrestricted', 'format_location');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Generate the html for a hidden section
     *
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return string HTML to output.
     */
    protected function location_section_notallowed($sectionno) {
        $o = '';
        $geo = format_location_icon();
        $o.= html_writer::start_tag('li', array('id' => 'section-' . $sectionno, 'class' => 'section main clearfix hidden'));
        $o.= html_writer::tag('div', '', array('class' => 'left side'));
        $o.= html_writer::tag('div', '', array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
        $o.= $geo;
        $o.= $this->output->spacer(array('width' => '10px'));
        $o.= get_string('notallowed', 'format_location');
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
        return $o;
    }

    /**
     * location_get_user_location
     *
     * Retrieve the user's location if stored in the database recently.
     *
     * @param int $userid
     * @return boolean or object $location
     */
    private function location_get_user_location($userid) {
        global $DB;
        $location = false;
        $elapsedtime = time() - 30;
        if (!$location = $DB->get_record_select('format_location_user', "userid = $userid AND timemodified > $elapsedtime")) {
            $location = false;
        }
        return $location;
    }

}