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
 * Prints a particular instance of a 3d model
 *
 * @package   mod_model
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/filelib.php');

global $DB;

$id = optional_param('id', 0, PARAM_INT);
$l = optional_param('l', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$search  = optional_param('search', '', PARAM_TEXT);
$editing = optional_param('editing', 0, PARAM_BOOL);

if ($id) {
    list($course, $cm) = get_course_and_cm_from_cmid($id, 'wavefront');
    if (!$wavefront = $DB->get_record('wavefront', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} else {
    if (!$wavefront = $DB->get_record('wavefront', array('id' => $l))) {
        print_error('invalidwavefrontid', 'wavefront');
    }
    list($course, $cm) = get_course_and_cm_from_instance($wavefront, 'wavefront');
}


if ($wavefront->ispublic) {
    $userid = (isloggedin() ? $USER->id : 0);
    $PAGE->set_cm($cm, $course);
    $PAGE->set_pagelayout('incourse');
} else {
    require_login($course, true, $cm);
    $userid = $USER->id;
}

$context = context_module::instance($cm->id);

if ($editing) {
    require_capability('mod/wavefront:edit', $context);
}

if (empty($cm->visible) and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string("activityiscurrentlyhidden"));
}

wavefront_config_defaults();

$params = array(
    'context' => $context,
    'objectid' => $wavefront->id
);
$event = \mod_wavefront\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('wavefront', $wavefront);
$event->trigger();

// Mark viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_cm($cm);
$PAGE->set_url('/mod/wavefront/view.php', array('id' => $cm->id));
$PAGE->set_title($wavefront->name);
$PAGE->set_heading($course->shortname);
$button = '';
if (has_capability('mod/wavefront:edit', $context)) {
    $urlparams = array('id' => $id, 'page' => $page, 'editing' => $editing ? '0' : '1');
    $url = new moodle_url('/mod/wavefront/view.php', $urlparams);
    $strediting = get_string('turnediting'.($editing ? 'off' : 'on'));
    $button = $OUTPUT->single_button($url, $strediting, 'get').' ';
}
$PAGE->set_button($button);

// The javascript this page requires
// The code we are using is neat javascript so load each script one at a time
//
$PAGE->requires->js('/mod/wavefront/thirdparty/three.js', true);
$PAGE->requires->js('/mod/wavefront/thirdparty/Detector.js', true);
$PAGE->requires->js('/mod/wavefront/thirdparty/OrbitControls.js', true);
$PAGE->requires->js('/mod/wavefront/thirdparty/OBJLoader.js', true);
$PAGE->requires->js('/mod/wavefront/thirdparty/MTLLoader.js', true);

$modelerr = true;

if ($model = $DB->get_record('wavefront_model', array('wavefrontid' => $wavefront->id))) {
    $fs = get_file_storage();
    $fs_files = $fs->get_area_files($context->id, 'mod_wavefront', 'model', $model->id, "itemid, filepath, filename", false);
    
    // A Wavefront model contains three files
    $mtl_file = null;
    $obj_file = null;
    $baseurl = null;
    
    foreach ($fs_files as $f) {
        // $f is an instance of stored_file
        $pathname = $f->get_filepath();
        $filename = $f->get_filename();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        // what type of file is this?
        if($ext === "mtl") {
            $mtl_file = moodle_url::make_pluginfile_url($context->id, 'mod_wavefront', 'model', $model->id, $pathname, $filename);
        } elseif ($ext === "obj") {
            $obj_file = moodle_url::make_pluginfile_url($context->id, 'mod_wavefront', 'model', $model->id, $pathname, $filename);
            $baseurl = moodle_url::make_pluginfile_url($context->id, 'mod_wavefront', 'model', $model->id, $pathname, '');  
        }
    }
    
    if($mtl_file != null && $obj_file != null) {
        $modelerr = false;
        
        $js_params = array('wavefront_stage', $obj_file->__toString(), $mtl_file->__toString(), $baseurl->__toString(),
                $model->stagewidth, $model->stageheight,
                $model->cameraangle, $model->camerafar, $model->camerax, $model->cameray, $model->cameraz);
         
        $PAGE->requires->js_call_amd('mod_wavefront/model_renderer', 'init', $js_params);
    }
}

$output = $PAGE->get_renderer('mod_wavefront');

echo $output->header();

$heading = get_string('displayingmodel', 'wavefront', $wavefront->name);
echo $output->heading($heading);

echo $output->display_model($wavefront, $editing, $modelerr);
echo $output->display_comments($wavefront, $editing);

echo $output->footer();

