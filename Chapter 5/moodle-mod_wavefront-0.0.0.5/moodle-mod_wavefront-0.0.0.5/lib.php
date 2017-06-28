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
 * Library of interface functions and constants for 3D Rendering module
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_wavefront
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once(dirname(__FILE__).'/locallib.php');

function wavefront_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;

        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $wavefront An object from the form in mod_form.php
 * @return int The id of the newly inserted newmodule record
 */
function wavefront_add_instance($wavefront) {
    global $DB;

    $wavefront->timemodified = time();

    wavefront_set_sizing($wavefront);

    return $DB->insert_record('wavefront', $wavefront);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $wavefront An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function wavefront_update_instance($wavefront) {
    global $DB;

    $wavefront->timemodified = time();
    $wavefront->id = $wavefront->instance;

    wavefront_set_sizing($wavefront);

    return $DB->update_record('wavefront', $wavefront);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function wavefront_delete_instance($id) {
    global $DB;

    if (!$wavefront = $DB->get_record('wavefront', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('wavefront', $wavefront->id);
    $context = context_module::instance($cm->id);
    // Files.
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_wavefront');

    // Delete all the records and models.
    $DB->delete_records('wavefront_comments', array('wavefrontid' => $wavefront->id) );
    $DB->delete_records('wavefront_model', array('wavefrontid' => $wavefront->id));

    // Delete the instance itself.
    $DB->delete_records('wavefront', array('id' => $id));

    return true;
}

/**
 * Given a model object from mod_form, determine the autoresize and resize params.
 *
 * @param object $wavefront
 * @return void
 */
function wavefront_set_sizing($wavefront) {
    if (isset($wavefront->autoresizedisabled)) {
        $wavefront->autoresize = 0;
        $wavefront->resize = 0;
    }
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function wavefront_user_complete($course, $user, $mod, $resource) {
    global $DB, $CFG;

    $sql = "SELECT c.*
              FROM {wavefront_comments} c
                   JOIN {wavefront} l ON l.id = c.wavefrontid
                   JOIN {user}            u ON u.id = c.userid
             WHERE l.id = :mod AND u.id = :userid
          ORDER BY c.timemodified ASC";
    $params = array('mod' => $mod->instance, 'userid' => $user->id);
    if ($comments = $DB->get_records_sql($sql, $params)) {
        $cm = get_coursemodule_from_id('wavefront', $mod->id);
        $context = context_module::instance($cm->id);
        foreach ($comments as $comment) {
            wavefront_print_comment($comment, $context);
        }
    } else {
        print_string('nocomments', 'wavefront');
    }
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function wavefront_get_extra_capabilities() {
    return array('moodle/course:viewhiddenactivities');
}

function wavefront_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    global $DB, $CFG, $COURSE;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $userfields = user_picture::fields('u', null, 'userid');
    $userfieldsnoalias = user_picture::fields();
    $sql = "SELECT c.*, l.name, $userfields
              FROM {wavefront_comments} c
                   JOIN {wavefront} l ON l.id = c.wavefrontid
                   JOIN {user}            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.id = {$cm->instance}
                   " . ($userid ? "AND u.id = $userid" : '') . "
          ORDER BY c.timemodified ASC";

    if ($comments = $DB->get_records_sql($sql)) {
        foreach ($comments as $comment) {
            $display = wavefront_resize_text(trim(strip_tags($comment->commenttext)), MAX_COMMENT_PREVIEW);

            $activity = new stdClass();

            $activity->type         = 'wavefront';
            $activity->cmid         = $cm->id;
            $activity->name         = format_string($cm->name, true);
            $activity->sectionnum   = $cm->sectionnum;
            $activity->timestamp    = $comment->timemodified;

            $activity->content = new stdClass();
            $activity->content->id      = $comment->id;
            $activity->content->comment = $display;

            $activity->user = new stdClass();
            $activity->user->id = $comment->userid;

            $fields = explode(',', $userfieldsnoalias);
            foreach ($fields as $field) {
                if ($field == 'id') {
                    continue;
                }
                $activity->user->$field = $comment->$field;
            }

            $activities[$index++] = $activity;

        }
    }
    return true;
}

function wavefront_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG, $OUTPUT;

    $userviewurl = new moodle_url('/user/view.php', array('id' => $activity->user->id, 'course' => $courseid));
    echo '<table border="0" cellpadding="3" cellspacing="0">'.
         '<tr><td class="userpicture" valign="top">'.$OUTPUT->user_picture($activity->user, array('courseid' => $courseid)).
         '</td><td>'.
         '<div class="title">'.
         ($detail ? '<img src="'.$CFG->modpixpath.'/'.$activity->type.'/icon.gif" class="icon" alt="'.s($activity->name).'" />' : ''
         ).
         '<a href="'.$CFG->wwwroot.'/mod/wavefront/view.php?id='.$activity->cmid.'#c'.$activity->content->id.'">'.
         $activity->content->comment.'</a>'.
         '</div>'.
         '<div class="user"> '.
         html_writer::link($userviewurl, fullname($activity->user, $viewfullnames)).
         ' - '.userdate($activity->timestamp).
         '</div>'.
         '</td></tr></table>';

    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function wavefront_print_recent_activity($course, $viewfullnames, $timestart) {
    global $DB, $CFG, $OUTPUT;

    $userfields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT c.*, l.name, $userfields
              FROM {wavefront_comments} c
                   JOIN {wavefront} l ON l.id = c.wavefrontid
                   JOIN {user}            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.course = {$course->id}
          ORDER BY c.timemodified ASC";

    if ($comments = $DB->get_records_sql($sql)) {
        echo $OUTPUT->heading(get_string('newmodelcomments', 'wavefront').':', 3);

        echo '<ul class="unlist">';

        foreach ($comments as $comment) {
            $display = wavefront_resize_text(trim(strip_tags($comment->commenttext)), MAX_COMMENT_PREVIEW);

            $output = '<li>'.
                 ' <div class="head">'.
                 '  <div class="date">'.userdate($comment->timemodified, get_string('strftimerecent')).'</div>'.
                 '  <div class="name">'.fullname($comment, $viewfullnames).' - '.format_string($comment->name).'</div>'.
                 ' </div>'.
                 ' <div class="info">'.
                 '  "<a href="'.$CFG->wwwroot.'/mod/wavefront/view.php?l='.$comment->wavefrontid.'#c'.$comment->id.'">'.
                 $display.'</a>"'.
                 ' </div>'.
                 '</li>';
            echo $output;
        }

        echo '</ul>';

    }

    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of newmodule. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function wavefront_get_participants($wavefrontid) {
    global $DB, $CFG;

    return $DB->get_records_sql("SELECT DISTINCT u.id, u.id
                                   FROM {user} u,
                                        {model_comments} c
                                  WHERE c.wavefrontid = $wavefrontid AND u.id = c.userid");
}

function wavefront_get_view_actions() {
    return array('view', 'view all', 'search');
}

function wavefront_get_post_actions() {
    return array('comment', 'addmodel', 'editmodel');
}

/**
 * Serves model files.
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function wavefront_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    require_once($CFG->libdir.'/filelib.php');

    $wavefront = $DB->get_record('wavefront', array('id' => $cm->instance));
    if (!$wavefront->ispublic) {
        require_login($course, false, $cm);
    }
    
    $relativepath = implode('/', $args);
    $fullpath = '/'.$context->id.'/mod_wavefront/'.$filearea.'/'.$relativepath;

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true); // Download MUST be forced - security!

    return;

}


/**
 * Lists all browsable file areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function wavefront_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['wavefront_files'] = get_string('modelfiles', 'model');

    return $areas;
}

/**
 * File browsing support for model module content area.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return object file_info instance or null if not found
 */
function wavefront_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if ($filearea === 'model_files') {
        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($context->id, 'mod_model', 'model_files', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_model', 'model_files', 0);
            } else {
                // Not found.
                return null;
            }
        }

        require_once("$CFG->dirroot/mod/model/locallib.php");
        $urlbase = $CFG->wwwroot.'/pluginfile.php';

        return new wavefront_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea],
                                                        true, true, false, false);
    }

    return null;
}

/**
 * Trim inputted text to the given maximum length.
 * @param string $text
 * @param int $length
 * @return string The trimmed string with a '...' appended for display.
 */
function wavefront_resize_text($text, $length) {
    return core_text::strlen($text) > $length ? core_text::substr($text, 0, $length) . '...' : $text;
}

/**
 * Output the HTML for a comment in the given context.
 * @param object $comment The comment record to output
 * @param object $context The context from which this is being displayed
 */
function wavefront_print_comment($comment, $context) {
    global $DB, $CFG, $COURSE, $OUTPUT;

    // TODO: Move to renderer!

    $user = $DB->get_record('user', array('id' => $comment->userid));

    $deleteurl = new moodle_url('/mod/wavefront/comment.php', array('id' => $comment->wavefrontid, 'delete' => $comment->id));

    echo '<table cellspacing="0" width="50%" class="boxaligncenter datacomment forumpost">'.
            '<tr class="header"><td class="picture left">'.$OUTPUT->user_picture($user, array('courseid' => $COURSE->id)).'</td>'.
            '<td class="topic starter" align="left"><a name="c'.$comment->id.'"></a><div class="author">'.
            '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$COURSE->id.'">'.
            fullname($user, has_capability('moodle/site:viewfullnames', $context)).'</a> - '.userdate($comment->timemodified).
            '</div></td></tr>'.
            '<tr><td class="left side">'.
            // TODO: user_group picture?
    '</td><td class="content" align="left">'.
    format_text($comment->commenttext, FORMAT_MOODLE).
    '<div class="commands">'.
    (has_capability('mod/wavefront:edit', $context) ? html_writer::link($deleteurl, get_string('delete')) : '').
    '</div>'.
    '</td></tr></table>';
}
