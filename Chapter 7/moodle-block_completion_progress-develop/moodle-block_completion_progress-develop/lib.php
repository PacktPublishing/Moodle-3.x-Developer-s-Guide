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
 * Completion Progress block common configuration and helper functions
 *
 * @package    block_completion_progress
 * @copyright  2016 Michael de Raadt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/completionlib.php');

// Global defaults.
const DEFAULT_COMPLETIONPROGRESS_WRAPAFTER = 16;
const DEFAULT_COMPLETIONPROGRESS_LONGBARS = 'squeeze';
const DEFAULT_COMPLETIONPROGRESS_SCROLLCELLWIDTH = 25;
const DEFAULT_COMPLETIONPROGRESS_COURSENAMETOSHOW = 'shortname';
const DEFAULT_COMPLETIONPROGRESS_SHOWINACTIVE = 0;
const DEFAULT_COMPLETIONPROGRESS_PROGRESSBARICONS = 0;
const DEFAULT_COMPLETIONPROGRESS_ORDERBY = 'orderbytime';
const DEFAULT_COMPLETIONPROGRESS_SHOWPERCENTAGE = 0;
const DEFAULT_COMPLETIONPROGRESS_ACTIVITIESINCLUDED = 'activitycompletion';

/**
 * Finds submissions for a user in a course
 *
 * @param int    courseid ID of the course
 * @param int    userid   ID of user in the course
 * @return array Course module IDS submissions
 */
function block_completion_progress_student_submissions($courseid, $userid) {
    global $DB;

    $submissions = array();
    $params = array('courseid' => $courseid, 'userid' => $userid);

    // Queries to deliver instance IDs of activities with submissions by user.
    $queries = array (
        'assign' => "SELECT c.id
                       FROM {assign_submission} s, {assign} a, {modules} m, {course_modules} c
                      WHERE s.userid = :userid
                        AND s.latest = 1
                        AND s.status = 'submitted'
                        AND s.assignment = a.id
                        AND a.course = :courseid
                        AND m.name = 'assign'
                        AND m.id = c.module
                        AND c.instance = a.id",
        'workshop' => "SELECT c.id
                         FROM {workshop_submissions} s, {workshop} w, {modules} m, {course_modules} c
                        WHERE s.authorid = :userid
                          AND s.workshopid = w.id
                          AND w.course = :courseid
                          AND m.name = 'workshop'
                          AND m.id = c.module
                          AND c.instance = w.id",
    );

    foreach ($queries as $moduletype => $query) {
        $results = $DB->get_records_sql($query, $params);
        foreach ($results as $cmid => $obj) {
            $submissions[] = $cmid;
        }
    }

    return $submissions;
}

/**
 * Finds submissions for users in a course
 *
 * @param int    courseid   ID of the course
 * @return array Mapping of userid-cmid pairs for submissions
 */
function block_completion_progress_course_submissions($courseid) {
    global $DB;

    $submissions = array();
    $params = array('courseid' => $courseid);

    // Queries to deliver instance IDs of activities with submissions by user.
    $queries = array (
        'assign' => "SELECT CONCAT(s.userid, '-', c.id)
                       FROM {assign_submission} s, {assign} a, {modules} m, {course_modules} c
                      WHERE s.latest = 1
                        AND s.status = 'submitted'
                        AND s.assignment = a.id
                        AND a.course = :courseid
                        AND m.name = 'assign'
                        AND m.id = c.module
                        AND c.instance = a.id",
        'workshop' => "SELECT CONCAT(s.authorid, '-', c.id)
                         FROM {workshop_submissions} s, {workshop} w, {modules} m, {course_modules} c
                        WHERE s.workshopid = w.id
                          AND w.course = :courseid
                          AND m.name = 'workshop'
                          AND m.id = c.module
                          AND c.instance = w.id",
    );

    foreach ($queries as $moduletype => $query) {
        $results = $DB->get_records_sql($query, $params);
        foreach ($results as $mapping => $obj) {
            $submissions[] = $mapping;
        }
    }

    return $submissions;
}

/**
 * Returns the alternate links for teachers
 *
 * @return array URLs and associated capabilities, per activity
 */
function block_completion_progress_modules_with_alternate_links() {
    global $CFG;

    $alternatelinks = array(
        'assign' => array(
            'url' => '/mod/assign/view.php?id=:cmid&action=grading',
            'capability' => 'mod/assign:grade',
        ),
        'feedback' => array(
            // Breaks if anonymous feedback is collected.
            'url' => '/mod/feedback/show_entries.php?id=:cmid&do_show=showoneentry&userid=:userid',
            'capability' => 'mod/feedback:viewreports',
        ),
        'lesson' => array(
            'url' => '/mod/lesson/report.php?id=:cmid&action=reportdetail&userid=:userid',
            'capability' => 'mod/lesson:viewreports',
        ),
        'quiz' => array(
            'url' => '/mod/quiz/report.php?id=:cmid&mode=overview',
            'capability' => 'mod/quiz:viewreports',
        ),
    );

    if ($CFG->version > 2015111604) {
        $alternatelinks['assign']['url'] = '/mod/assign/view.php?id=:cmid&action=grade&userid=:userid';
    }

    return $alternatelinks;
}

/**
 * Returns the activities with completion set in current course
 *
 * @param int    courseid   ID of the course
 * @param int    config     The block instance configuration
 * @param string forceorder An override for the course order setting
 * @return array Activities with completion settings in the course
 */
function block_completion_progress_get_activities($courseid, $config = null, $forceorder = null) {
    $modinfo = get_fast_modinfo($courseid, -1);
    $sections = $modinfo->get_sections();
    $activities = array();
    foreach ($modinfo->instances as $module => $instances) {
        $modulename = get_string('pluginname', $module);
        foreach ($instances as $index => $cm) {
            if (
                $cm->completion != COMPLETION_TRACKING_NONE && (
                    $config == null || (
                        !isset($config->activitiesincluded) || (
                            $config->activitiesincluded != 'selectedactivities' ||
                                in_array($module.'-'.$cm->instance, $config->selectactivities))))
            ) {
                $activities[] = array (
                    'type'       => $module,
                    'modulename' => $modulename,
                    'id'         => $cm->id,
                    'instance'   => $cm->instance,
                    'name'       => $cm->name,
                    'expected'   => $cm->completionexpected,
                    'section'    => $cm->sectionnum,
                    'position'   => array_search($cm->id, $sections[$cm->sectionnum]),
                    'url'        => method_exists($cm->url, 'out') ? $cm->url->out() : '',
                    'context'    => $cm->context,
                    'icon'       => $cm->get_icon_url(),
                    'available'  => $cm->available,
                );
            }
        }
    }

    // Sort by first value in each element, which is time due.
    if ($forceorder == 'orderbycourse' || ($config && $config->orderby == 'orderbycourse')) {
        usort($activities, 'block_completion_progress_compare_events');
    } else {
        usort($activities, 'block_completion_progress_compare_times');
    }

    return $activities;
}

/**
 * Used to compare two activities/resources based on order on course page
 *
 * @param array $a array of event information
 * @param array $b array of event information
 * @return <0, 0 or >0 depending on order of activities/resources on course page
 */
function block_completion_progress_compare_events($a, $b) {
    if ($a['section'] != $b['section']) {
        return $a['section'] - $b['section'];
    } else {
        return $a['position'] - $b['position'];
    }
}

/**
 * Used to compare two activities/resources based their expected completion times
 *
 * @param array $a array of event information
 * @param array $b array of event information
 * @return <0, 0 or >0 depending on time then order of activities/resources
 */
function block_completion_progress_compare_times($a, $b) {
    if (
        $a['expected'] != 0 &&
        $b['expected'] != 0 &&
        $a['expected'] != $b['expected']
    ) {
        return $a['expected'] - $b['expected'];
    } else if ($a['expected'] != 0 && $b['expected'] == 0) {
        return -1;
    } else if ($a['expected'] == 0 && $b['expected'] != 0) {
        return 1;
    } else {
        return block_completion_progress_compare_events($a, $b);
    }
}

/**
 * Filters activities that a user cannot see due to grouping constraints
 *
 * @param array  $activities The possible activities that can occur for modules
 * @param array  $userid The user's id
 * @param string $courseid the course for filtering visibility
 * @return array The array with restricted activities removed
 */
function block_completion_progress_filter_visibility($activities, $userid, $courseid) {
    global $CFG;
    $filteredactivities = array();
    $modinfo = get_fast_modinfo($courseid, $userid);
    $coursecontext = CONTEXT_COURSE::instance($courseid);

    // Keep only activities that are visible.
    foreach ($activities as $index => $activity) {

        $coursemodule = $modinfo->cms[$activity['id']];

        // Check visibility in course.
        if (!$coursemodule->visible && !has_capability('moodle/course:viewhiddenactivities', $coursecontext, $userid)) {
            continue;
        }

        // Check availability, allowing for visible, but not accessible items.
        if (!empty($CFG->enableavailability)) {
            if (has_capability('moodle/course:viewhiddenactivities', $coursecontext, $userid)) {
                $activity['available'] = true;
            } else {
                if (isset($coursemodule->available) && !$coursemodule->available && empty($coursemodule->availableinfo)) {
                    continue;
                }
                $activity['available'] = $coursemodule->available;
            }
        }

        // Check visibility by grouping constraints (includes capability check).
        if (!empty($CFG->enablegroupmembersonly)) {
            if (isset($coursemodule->uservisible)) {
                if ($coursemodule->uservisible != 1 && empty($coursemodule->availableinfo)) {
                    continue;
                }
            } else if (!groups_course_module_visible($coursemodule, $userid)) {
                continue;
            }
        }

        // Save the visible event.
        $filteredactivities[] = $activity;
    }
    return $filteredactivities;
}

/**
 * Checked if a user has completed an activity/resource
 *
 * @param array $activities  The activities with completion in the course
 * @param int   $userid      The user's id
 * @param int   $course      The course instance
 * @param array $submissions Submissions by the user
 * @return array   an describing the user's attempts based on module+instance identifiers
 */
function block_completion_progress_completions($activities, $userid, $course, $submissions) {
    $completions = array();
    $completion = new completion_info($course);
    $cm = new stdClass();

    foreach ($activities as $activity) {
        $cm->id = $activity['id'];
        $activitycompletion = $completion->get_data($cm, true, $userid);
        $completions[$activity['id']] = $activitycompletion->completionstate;
        if ($completions[$activity['id']] === COMPLETION_INCOMPLETE && in_array($activity['id'], $submissions)) {
            $completions[$activity['id']] = 'submitted';
        }
    }

    return $completions;
}

/**
 * json encode progress data
 *
 * @param array    $activities  The activities with completion in the course
 * @param array    $completions The user's completion of course activities
 * @param stdClass $config      The blocks instance configuration settings
 * @param int      $userid      The user's id
 * @param int      $courseid    The course id
 * @param int      instance     The block instance (to identify it on page)
 * @param bool     $simple      Controls whether instructions are shown below a progress bar
 * @return string json 
 */
function block_completion_progress_json($activities, $completions, $config, $userid, $courseid, $instance, $simple = false) {
    global $OUTPUT, $USER;
    
    // create a json array of activities
    $progress = array();
    
    // Get colours and use defaults if they are not set in global settings.
    $colornames = array(
            'completed_colour' => 'completed_colour',
            'submittednotcomplete_colour' => 'submittednotcomplete_colour',
            'notCompleted_colour' => 'notCompleted_colour',
            'futureNotCompleted_colour' => 'futureNotCompleted_colour'
    );
    $colors = array();
    foreach ($colornames as $name => $stringkey) {
        $colors[$name] = get_config('block_completion_progress', $name) ?: get_string('block_completion_progress', $stringkey);
    }
    
    $progress['colors'] = $colors;

    // Determine links to activities.
    $alternatelinks = block_completion_progress_modules_with_alternate_links();
    
    $numactivities = count($activities);
    
    for ($i = 0; $i < $numactivities; $i++) {
        if ($userid != $USER->id &&
                array_key_exists($activities[$i]['type'], $alternatelinks) &&
                has_capability($alternatelinks[$activities[$i]['type']]['capability'], $activities[$i]['context'])
                ) {
                    $substitutions = array(
                            '/:courseid/' => $courseid,
                            '/:eventid/'  => $activities[$i]['instance'],
                            '/:cmid/'     => $activities[$i]['id'],
                            '/:userid/'   => $userid,
                    );
                    $link = $alternatelinks[$activities[$i]['type']]['url'];
                    $link = preg_replace(array_keys($substitutions), array_values($substitutions), $link);
                    $activities[$i]['link'] = $CFG->wwwroot.$link;
                } else {
                    $activities[$i]['link'] = $activities[$i]['url'];
                }
    }

    $completed_activities = 0;
    
    foreach ($activities as $activity) {
        $activity_details = array();
        
        $completed = $completions[$activity['id']];
        
        $activity_details['name'] = s($activity['name']);
        
        if (!empty($activity['link']) && (!empty($activity['available']) || $simple)) {
            $activity_details['link'] = $activity['link'];
        } else {
            $activity_details['link'];
        }
        
        $activity_details['status'] = '';
        
        if ($completed == COMPLETION_COMPLETE) {
            $activity_details['status'] = 'complete';
            $completed_activities++;
        } else if ($completed == COMPLETION_COMPLETE_PASS) {
            $activity_details['status'] = 'passed';
            $completed_activities++;
        } else if ($completed == COMPLETION_COMPLETE_FAIL) {
            $activity_details['status'] = 'failed';
        } else {
            if ($completed === 'submitted') {
                $activity_details['status'] = 'submitted';
            }
        }
        
        $progress['activities'][] = $activity_details;
    }
    
    $percentage = 0;
    if ( $numactivities> 0 ) {
        $percentage = round($completed_activities/ ($numactivities/ 100),2);
    }
    
    $progress['percentage'] = (string)$percentage;
    
    $data = json_encode($progress);
    
    return $data;
}

/**
 * Checks whether the current page is the My home page.
 *
 * @return bool True when on the My home page.
 */
function block_completion_progress_on_site_page() {
    global $SCRIPT, $COURSE;

    return $SCRIPT === '/my/index.php' || $COURSE->id == 1;
}
