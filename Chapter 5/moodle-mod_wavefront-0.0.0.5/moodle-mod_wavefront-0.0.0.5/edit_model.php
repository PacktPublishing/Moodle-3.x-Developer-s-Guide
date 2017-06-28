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
 * Form for adding comments on a model
 *
 * @package   mod_wavefront
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('edit_model_form.php');

$cmid = required_param('cmid', PARAM_INT);            // Course Module ID
$id   = optional_param('id', 0, PARAM_INT);           // Wavefront ID

if (!$cm = get_coursemodule_from_id('wavefront', $cmid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('coursemisconf');
}

$context = context_module::instance($cm->id);

require_capability('mod/wavefront:edit', $context);

if (!$wavefront = $DB->get_record('wavefront', array('id'=>$cm->instance))) {
    print_error('invalidid', 'wavefront');
}

$url = new moodle_url('/mod/wavefront/edit.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);

require_login($course, false, $cm);

// attempt to get the correct model
$model = $DB->get_record('wavefront_model', array('wavefrontid'=>$wavefront->id));
     
if($model) {     
    if (isguestuser()) {
        print_error('guestnoedit', 'wavefront', "$CFG->wwwroot/mod/wavefront/view.php?id=$cmid");
    }
    
} else { // new entry? Or something has gone horribly wrong
    $model = new stdClass();
    $model->id = null;
}

$maxfiles = 50;                // TODO: add some setting
$maxbytes = $course->maxbytes; // TODO: add some setting

$descriptionoptions = array('trusttext'=>true, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'context'=>$context,
    'subdirs'=>file_area_contains_subdirs($context, 'mod_wavefront', 'model', $model->id));
$modeloptions = array('subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes);

$model = file_prepare_standard_editor($model, 'description', $descriptionoptions, $context, 'mod_wavefront', 'description', $model->id);
$model = file_prepare_standard_filemanager($model, 'model', $modeloptions, $context, 'mod_wavefront', 'model', $model->id);

$model->cmid = $cm->id;

// create form and set initial data
$mform = new mod_wavefront_model_form(null, array('model'=>$model, 'cm'=>$cm, 'descriptionoptions'=>$descriptionoptions, 
                                                    'modeloptions'=>$modeloptions));

if ($mform->is_cancelled()){
    if ($id){
        redirect("view.php?id=$cm->id&mode=entry&hook=$id");
    } else {
        redirect("view.php?id=$cm->id");
    }

} else if ($model = $mform->get_data()) {
    $timenow = time();

    if (empty($model->id)) {
        $model->wavefrontid      = $wavefront->id;
        $model->userid           = $USER->id;
        $model->timecreated      = $timenow;

        $isnewentry              = true;
    } else {
        $isnewentry              = false;
    }

    $model->description      = '';          // updated later
    $model->descriptionformat = FORMAT_HTML; // updated later
    $model->definitiontrust  = 0;           // updated later
    $model->timemodified     = $timenow;
    
    if ($isnewentry) {
        // Add new entry.
        $model->id = $DB->insert_record('wavefront_model', $model);
    } else {
        // Update existing entry.
        $DB->update_record('wavefront', $model);
    }

    // save and relink embedded images and save attachments
    $model = file_postupdate_standard_editor($model, 'description', $descriptionoptions, $context, 'mod_wavefront', 'description', $model->id);
    $model = file_postupdate_standard_filemanager($model, 'model', $modeloptions, $context, 'mod_wavefront', 'model', $model->id);
    
    wavefront_check_for_zips($context, $cm, $model);
    
    // store the updated value values
    $DB->update_record('wavefront_model', $model);

    //refetch complete entry
    $model = $DB->get_record('wavefront_model', array('id'=>$model->id));

    // Trigger event and update completion (if entry was created).
    $eventparams = array(
        'context' => $context,
        'objectid' => $model->id,
    );
    if ($isnewentry) {
        $event = \mod_wavefront\event\model_created::create($eventparams);
    } else {
        $event = \mod_wavefront\event\model_updated::create($eventparams);
    }
    $event->add_record_snapshot('wavefront', $wavefront);
    $event->trigger();
    if ($isnewentry) {
        // Update completion state
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $wavefront->completionentries) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }
    }
    
    redirect("view.php?id=$cm->id&editing=1");
}

if (!empty($id)) {
    $PAGE->navbar->add(get_string('edit'));
}

$PAGE->set_title($wavefront->name);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($wavefront->name), 2);
if ($wavefront->intro) {
    echo $OUTPUT->box(format_module_intro('wavefront', $wavefront, $cm->id), 'generalbox', 'intro');
}

$mform->display();

echo $OUTPUT->footer();

