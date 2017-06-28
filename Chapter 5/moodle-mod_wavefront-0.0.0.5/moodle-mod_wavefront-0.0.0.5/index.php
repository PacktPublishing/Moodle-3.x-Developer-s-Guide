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
 * Shows a list of available models
 *
 * @package   mod_wavefront
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$context = context_course::instance($course->id);
require_course_login($course);

$event = \mod_wavefront\event\course_module_instance_list_viewed::create(array(
    'context' => $context
));
$event->add_record_snapshot('course', $course);
$event->trigger();

$PAGE->set_url('/mod/wavefront/view.php', array('id' => $id));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

if (! $models = get_all_instances_in_course('wavefront', $course)) {
    echo $OUTPUT->heading(get_string('thereareno', 'moodle', $strmodels), 2);
    echo $OUTPUT->continue_button('view.php?id='.$course->id);
    echo $OUTPUT->footer();
    die();
}

$table = new html_table();
$table->head = array(get_string($course->format == 'weeks' ? 'week' : 'topic'),
                        '&nbsp;',
                        get_string('modulenameshort', 'model'),
                        get_string('description'),
                        'RSS');
$table->align = array('center', 'center', 'left', 'left', 'center');
$table->width = '*';

$fobj = new stdClass;
$fobj->para = false;

$prevsection = '';

// TODO: Put this in a renderer.
foreach ($models as $model) {
    $cm = context_module::instance($model->coursemodule);

    $printsection = ($model->section !== $prevsection ? true : false);
    if ($printsection) {
        $table->data[] = 'hr';
    }

    $fs = get_file_storage();
    $files = $fs->get_area_files($cm->id, 'mod_wavefront', 'model');
    $modelcount = 0;
    foreach ($files as $file) {
        if ($file->get_filename() != '.') {
            $modelcount++;
        }
    }
    $commentcount = $DB->count_records('wavefront_comments', array('model' => $model->id));

    $viewurl = new moodle_url('/mod/wavefront/view.php', array('id' => $model->coursemodule));
    $table->data[] = array(($printsection ? $model->section : ''),
                           model_index_thumbnail($course->id, $model),
                           html_writer::link($viewurl, $model->name).
                           '<br />'.get_string('modelcounta', 'model', $modelcount).' '.
                           get_string('modelcount', 'model', $commentcount),
                           format_text($model->intro, FORMAT_MOODLE, $fobj));
                           
    $prevsection = $model->section;
}

echo $OUTPUT->heading(get_string('modulenameplural', 'model'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();

