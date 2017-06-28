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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/comment_form.php');

$id      = required_param('id', PARAM_INT);
$delete  = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

if (!$wavefront = $DB->get_record('wavefront', array('id' => $id))) {
    print_error('invalidwavefrontid', 'wavefront');
}
list($course, $cm) = get_course_and_cm_from_instance($wavefront, 'wavefront');

if ($delete && ! $comment = $DB->get_record('wavefront_comments', array('wavefrontid' => $wavefront->id, 'id' => $delete))) {
    print_error('Invalid comment ID');
}

require_login($course, true, $cm);

$PAGE->set_cm($cm);
$PAGE->set_url('/mod/wavefront/view.php', array('id' => $id));
$PAGE->set_title($wavefront->name);
$PAGE->set_heading($course->shortname);

$context = context_module::instance($cm->id);

$wavefronturl = $CFG->wwwroot.'/mod/wavefront/view.php?id='.$cm->id;

if ($delete && has_capability('mod/wavefront:edit', $context)) {
    if ($confirm && confirm_sesskey()) {
        $DB->delete_records('wavefront_comments', array('id' => $comment->id));
        redirect($wavefronturl);
    } else {
        echo $OUTPUT->header();
        wavefront_print_comment($comment, $context);
        echo('<br />');
        $paramsyes = array('id' => $wavefront->id, 'delete' => $comment->id, 'sesskey' => sesskey(), 'confirm' => 1);
        $paramsno = array('id' => $cm->id);
        echo $OUTPUT->confirm(get_string('commentdelete', 'wavefront'),
                              new moodle_url('/mod/wavefront/comment.php', $paramsyes),
                              new moodle_url('/mod/wavefront/view.php', $paramsno));
        echo $OUTPUT->footer();
        die();
    }
}

require_capability('mod/wavefront:addcomment', $context);

if (! $wavefront->comments) {
    print_error('Comments disabled', $wavefronturl);
}

$mform = new mod_wavefront_comment_form(null, $wavefront);

if ($mform->is_cancelled()) {
    redirect($wavefronturl);
} else if ($formadata = $mform->get_data()) {
    $newcomment = new stdClass;
    $newcomment->wavefrontid = $wavefront->id;
    $newcomment->userid = $USER->id;
    $newcomment->commenttext = $formadata->comment['text'];
    $newcomment->timemodified = time();
    if ($DB->insert_record('wavefront_comments', $newcomment)) {
        $params = array(
            'context' => $context,
            'other' => array(
                'wavefrontid' => $wavefront->id,
            ),
        );
        $event = \mod_wavefront\event\wavefront_comment_created::create($params);
        $event->trigger();

        redirect($wavefronturl, get_string('commentadded', 'wavefront'));
    } else {
        print_error('Comment creation failed');
    }
}


echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
