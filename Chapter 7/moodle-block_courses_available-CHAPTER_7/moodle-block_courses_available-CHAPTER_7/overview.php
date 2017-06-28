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
 * Completion Progress block overview page
 *
 * @package    block_completion_progress
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// Displays external information about a course

require_once("../../config.php");

$id   = optional_param('id', false, PARAM_INT); // Course id
$name = optional_param('name', false, PARAM_RAW); // Course short name

if (!$id and !$name) {
    print_error("unspecifycourseid");
}

if ($name) {
    if (!$course = $DB->get_record("course", array("shortname"=>$name))) {
        print_error("invalidshortname");
    }
} else {
    if (!$course = $DB->get_record("course", array("id"=>$id))) {
        print_error("invalidcourseid");
    }
}

$site = get_site();

if ($CFG->forcelogin) {
    require_login();
}

$context = context_course::instance($course->id);
if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $context)) {
    print_error('coursehidden', '', $CFG->wwwroot .'/');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/blocks/courses_available/overview.php', array('id' => $course->id));
$PAGE->set_title(get_string("summaryof", "", $course->fullname));
$PAGE->set_heading(get_string('courseinfoheading', 'block_courses_available'));
$PAGE->set_course($course);
$PAGE->navbar->add(get_string('description', 'block_courses_available'));

echo $OUTPUT->header();

echo $OUTPUT->box_start('generalbox summaryinfo');

echo format_text($course->summary, $course->summaryformat, array('overflowdiv'=>true), $course->id);

if (!empty($CFG->coursecontact)) {
    $coursecontactroles = explode(',', $CFG->coursecontact);
    foreach ($coursecontactroles as $roleid) {
        $role = $DB->get_record('role', array('id'=>$roleid));
        $roleid = (int) $roleid;
        if ($users = get_role_users($roleid, $context, true)) {
            foreach ($users as $teacher) {
                $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context));
                $namesarray[] = format_string(role_get_name($role, $context)).': <a href="'.$CFG->wwwroot.'/user/view.php?id='.
                        $teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
            }
        }
    }
    
    if (!empty($namesarray)) {
        echo "<ul class=\"teachers\">\n<li>";
        echo implode('</li><li>', $namesarray);
        echo "</li></ul>";
    }
}


echo $OUTPUT->box_end();

/*
 * button box
 */
$buttonBox = $OUTPUT->box_start('generalbox icons');
$cancel = new single_button(new moodle_url($CFG->wwwroot.'/my'), get_string('homepage', 'block_courses_available'), 'get');
$url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id'=>$course->id));
$continue = new single_button($url, get_string('coursepage', 'block_courses_available'), 'get');

$attr = array('id'=>'summarybuttons','class' => 'buttons');
$buttonBox .= html_writer::tag('div', $OUTPUT->render($continue).$OUTPUT->render($cancel), $attr);
$buttonBox .= $OUTPUT->box_end();
echo $buttonBox;

echo $OUTPUT->footer();