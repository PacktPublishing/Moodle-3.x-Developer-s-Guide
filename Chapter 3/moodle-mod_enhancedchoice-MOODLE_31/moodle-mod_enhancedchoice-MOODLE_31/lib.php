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
 * @package   mod-enhancedchoice
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}, Ian David Wild {@link http://heavy-horse.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @global int $ENHANCEDCHOICE_COLUMN_HEIGHT */
global $ENHANCEDCHOICE_COLUMN_HEIGHT;
$ENHANCEDCHOICE_COLUMN_HEIGHT = 300;

/** @global int $ENHANCEDCHOICE_COLUMN_WIDTH */
global $ENHANCEDCHOICE_COLUMN_WIDTH;
$ENHANCEDCHOICE_COLUMN_WIDTH = 300;

define('ENHANCEDCHOICE_PUBLISH_ANONYMOUS', '0');
define('ENHANCEDCHOICE_PUBLISH_NAMES',     '1');

define('ENHANCEDCHOICE_SHOWRESULTS_NOT',          '0');
define('ENHANCEDCHOICE_SHOWRESULTS_AFTER_ANSWER', '1');
define('ENHANCEDCHOICE_SHOWRESULTS_AFTER_CLOSE',  '2');
define('ENHANCEDCHOICE_SHOWRESULTS_ALWAYS',       '3');

define('ENHANCEDCHOICE_DISPLAY_HORIZONTAL',  '0');
define('ENHANCEDCHOICE_DISPLAY_VERTICAL',    '1');

/** @global array $ENHANCEDCHOICE_PUBLISH */
global $ENHANCEDCHOICE_PUBLISH;
$ENHANCEDCHOICE_PUBLISH = array (ENHANCEDCHOICE_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'enhancedchoice'),
                         ENHANCEDCHOICE_PUBLISH_NAMES      => get_string('publishnames', 'enhancedchoice'));

/** @global array $ENHANCEDCHOICE_SHOWRESULTS */
global $ENHANCEDCHOICE_SHOWRESULTS;
$ENHANCEDCHOICE_SHOWRESULTS = array (ENHANCEDCHOICE_SHOWRESULTS_NOT          => get_string('publishnot', 'enhancedchoice'),
                         ENHANCEDCHOICE_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer', 'enhancedchoice'),
                         ENHANCEDCHOICE_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose', 'enhancedchoice'),
                         ENHANCEDCHOICE_SHOWRESULTS_ALWAYS       => get_string('publishalways', 'enhancedchoice'));

/** @global array $CHOICE_DISPLAY */
global $ENHANCEDCHOICE_DISPLAY;
$ENHANCEDCHOICE_DISPLAY = array (ENHANCEDCHOICE_DISPLAY_HORIZONTAL   => get_string('displayhorizontal', 'enhancedchoice'),
                         ENHANCEDCHOICE_DISPLAY_VERTICAL     => get_string('displayvertical','enhancedchoice'));

/// Standard functions /////////////////////////////////////////////////////////

/**
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $choice
 * @return object|null
 */
function enhancedchoice_user_outline($course, $user, $mod, $choice) {
    global $DB;
    if ($answer = $DB->get_record('enhancedchoice_answers', array('choiceid' => $choice->id, 'userid' => $user->id))) {
        $result = new stdClass();
        $result->info = "'".format_string(enhancedchoice_get_option_text($choice, $answer->optionid))."'";
        $result->time = $answer->timemodified;
        return $result;
    }
    return NULL;
}

/**
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $choice
 * @return string|void
 */
function enhancedchoice_user_complete($course, $user, $mod, $choice) {
    global $DB;
    if ($answer = $DB->get_record('enhancedchoice_answers', array("choiceid" => $choice->id, "userid" => $user->id))) {
        $result = new stdClass();
        $result->info = "'".format_string(enhancedchoice_get_option_text($choice, $answer->optionid))."'";
        $result->time = $answer->timemodified;
        echo get_string("answered", "enhancedchoice").": $result->info. ".get_string("updated", '', userdate($result->time));
    } else {
        print_string("notanswered", "enhancedchoice");
    }
}

/**
 * This gets an array with default options for the editor
 *
 * @return array the options
 */
function enhancedchoice_get_editor_options() {
	return array('maxfiles' => EDITOR_UNLIMITED_FILES,
			'trusttext'=>true);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $choice
 * @return int
 */
function enhancedchoice_add_instance($choice) {
    global $DB;

    $choice->timemodified = time();

    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

    //insert answers
    $choice->id = $DB->insert_record("enhancedchoice", $choice);
    
    $context = context_module::instance($choice->coursemodule);
    $editoroptions = enhancedchoice_get_editor_options();
    
    foreach ($choice->option as $key => $value) {
        if (isset($value) && $value['text'] <> '') {
        	$value['text'] = trim($value['text']);
            $option = new stdClass();
            
            $option->text = $value['text'];
            $option->textformat = $value['format'];
            $option->choiceid = $choice->id;
            if (isset($choice->limit[$key])) {
                $option->maxanswers = $choice->limit[$key];
            }
            $option->timemodified = time();
            $option->id = $DB->insert_record("enhancedchoice_options", $option);
            $option->text = file_save_draft_area_files($value['itemid'], $context->id,
            		'mod_enhancedchoice', 'option',
            		$option->id, $editoroptions, $value['text']);
            $DB->set_field("enhancedchoice_options", "text", $option->text, array('id' => $option->id));
            
        }
    }

    return $choice->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $choice
 * @return bool
 */
function enhancedchoice_update_instance($choice) {
    global $DB;

    $choice->id = $choice->instance;
    $choice->timemodified = time();

    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

    $context = context_module::instance($choice->coursemodule);
    $editoroptions = enhancedchoice_get_editor_options();
    
    //update, delete or insert answers
    foreach ($choice->option as $key => $value) {
        $value['text'] = trim($value['text']);
        $option = new stdClass();
        // process the custom wysiwyg editor uploaded files
        $option->text = $value['text'];
        $option->textformat = $value['format'];
        $option->choiceid = $choice->id;
        $option->id=$choice->optionid[$key];
        
        if (isset($choice->limit[$key])) {
            $option->maxanswers = $choice->limit[$key];
        }
        $option->timemodified = time();
        $option->text = file_save_draft_area_files($value['itemid'], $context->id,
        		'mod_enhancedchoice', 'option',
        		$option->id, $editoroptions, $value['text']);
        
        if (isset($choice->optionid[$key]) && !empty($choice->optionid[$key])){//existing choice record
            if (isset($value) && $value['text'] <> '') {
                $DB->update_record("enhancedchoice_options", $option);
            } else { //empty old option - needs to be deleted.
                $DB->delete_records("enhancedchoice_options", array("id"=>$option->id));
                continue;
            }
        } else {
            if (isset($value) && $value['text'] <> '') {
                $DB->insert_record("enhancedchoice_options", $option);
            }
        }
        
        
    }

    return $DB->update_record('enhancedchoice', $choice);

}

/**
 * @global object
 * @param object $choice
 * @param object $user
 * @param object $coursemodule
 * @param array $allresponses
 * @return array
 */
function enhancedchoice_prepare_options($choice, $user, $coursemodule, $allresponses) {
    global $DB, $CFG;

    $cdisplay = array('options'=>array());

    $cdisplay['limitanswers'] = true;
    $context = context_module::instance($coursemodule->id);
    $filearea = 'option';
    
    foreach ($choice->option as $optionid => $text) {
        if (isset($text)) { //make sure there are no dud entries in the db with blank text values.
            $option = new stdClass;
            $option->attributes = new stdClass;
            $option->attributes->value = $optionid;
            
            $output = file_rewrite_pluginfile_urls($text,
            		'pluginfile.php',
            		$context->id,
            		'mod_enhancedchoice',
            		$filearea,
            		$optionid);
            
            $formatoptions = array('overflowdiv'=>true, 'trusted'=>$CFG->enabletrusttext);
            
            $option->text = format_text($output, FORMAT_HTML, $formatoptions);
            $option->maxanswers = $choice->maxanswers[$optionid];
            $option->displaylayout = $choice->display;

            if (isset($allresponses[$optionid])) {
                $option->countanswers = count($allresponses[$optionid]);
            } else {
                $option->countanswers = 0;
            }
            if ($DB->record_exists('enhancedchoice_answers', array('choiceid' => $choice->id, 'userid' => $user->id, 'optionid' => $optionid))) {
                $option->attributes->checked = true;
            }
            if ( $choice->limitanswers && ($option->countanswers >= $option->maxanswers) && empty($option->attributes->checked)) {
                $option->attributes->disabled = true;
            }
            $cdisplay['options'][] = $option;
        }
    }

    $cdisplay['hascapability'] = is_enrolled($context, NULL, 'mod/enhancedchoice:choose'); //only enrolled users are allowed to make a choice

    if ($choice->allowupdate && $DB->record_exists('choice_answers', array('choiceid'=> $choice->id, 'userid'=> $user->id))) {
        $cdisplay['allowupdate'] = true;
    }

    return $cdisplay;
}

/**
 * @global object
 * @param int $formanswer
 * @param object $choice
 * @param int $userid
 * @param object $course Course object
 * @param object $cm
 */
function enhancedchoice_user_submit_response($formanswer, $choice, $userid, $course, $cm) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    $current = $DB->get_record('enhancedchoice_answers', array('choiceid' => $choice->id, 'userid' => $userid));
    $context = context_module::instance($cm->id);

    $countanswers=0;
    if($choice->limitanswers) {
        // Find out whether groups are being used and enabled
        if (groups_get_activity_groupmode($cm) > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }
        if($currentgroup) {
            // If groups are being used, retrieve responses only for users in
            // current group
            global $CFG;
            $answers = $DB->get_records_sql("
SELECT
    ca.*
FROM
    {enhancedchoice_answers} ca
    INNER JOIN {groups_members} gm ON ca.userid=gm.userid
WHERE
    optionid=?
    AND gm.groupid=?", array($formanswer, $currentgroup));
        } else {
            // Groups are not used, retrieve all answers for this option ID
            $answers = $DB->get_records("enhancedchoice_answers", array("optionid" => $formanswer));
        }

        if ($answers) {
            foreach ($answers as $a) { //only return enrolled users.
                if (is_enrolled($context, $a->userid, 'mod/enhancedchoice:choose')) {
                    $countanswers++;
                }
            }
        }
        $maxans = $choice->maxanswers[$formanswer];
    }

    if (!($choice->limitanswers && ($countanswers >= $maxans) )) {
        $answerupdated = false;
        $answersnapshots = array();
        if ($current) {
            $newanswer = $current;
            $newanswer->optionid = $formanswer;
            $newanswer->timemodified = time();
            $DB->update_record("enhancedchoice_answers", $newanswer);
            $answerupdated = true;
            $answersnapshots[] = $newanswer;
        } else {
            $newanswer = new stdClass();
            $newanswer->choiceid = $choice->id;
            $newanswer->userid = $userid;
            $newanswer->optionid = $formanswer;
            $newanswer->timemodified = time();
            $DB->insert_record("enhancedchoice_answers", $newanswer);
            $answersnapshots[] = $newanswer;

            // Update completion state
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $choice->completionsubmit) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }
            
        }
        // Now record completed event.
        $eventdata = array();
        $eventdata['context'] = $context;
        $eventdata['objectid'] = $choice->id;
        $eventdata['userid'] = $userid;
        $eventdata['courseid'] = $course->id;
        $eventdata['other'] = array();
        $eventdata['other']['choiceid'] = $choice->id;
    
        if ($answerupdated) {
            $eventdata['other']['optionid'] = $formanswer;
            $event = \mod_choice\event\answer_updated::create($eventdata);
        } else {
            $eventdata['other']['optionid'] = $answers;
            $event = \mod_choice\event\answer_submitted::create($eventdata);
        }
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('enhancedchoice', $choice);
        foreach ($answersnapshots as $record) {
            $event->add_record_snapshot('enhancedchoice_answers', $record);
        }
        $event->trigger();
    
    } else {
        if (!($current->optionid==$formanswer)) { //check to see if current choice already selected - if not display error
            print_error('choicefull', 'enhancedchoice');
        }
    }
   
}

/**
 * @param array $user
 * @param object $cm
 * @return void Output is echo'd
 */
function enhancedchoice_show_reportlink($user, $cm) {
    $responsecount =0;
    foreach($user as $optionid => $userlist) {
        if ($optionid) {
            $responsecount += count($userlist);
        }
    }

    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "enhancedchoice", $responsecount)."</a>";
    echo '</div>';
}

/**
 * @global object
 * @param object $choice
 * @param object $course
 * @param object $coursemodule
 * @param array $allresponses

 *  * @param bool $allresponses
 * @return object
 */
function prepare_enhancedchoice_show_results($choice, $course, $cm, $allresponses, $forcepublish=false) {
    global $CFG, $ENHANCEDCHOICE_COLUMN_HEIGHT, $FULLSCRIPT, $PAGE, $OUTPUT, $DB;

    $display = clone($choice);
    $display->coursemoduleid = $cm->id;
    $display->courseid = $course->id;

    //overwrite options value;
    $display->options = array();
    $totaluser = 0;
    $filearea = 'option';
    
    $context = context_module::instance($cm->id);
    
    foreach ($choice->option as $optionid => $optiontext) {
        $display->options[$optionid] = new stdClass;
        $output = file_rewrite_pluginfile_urls($optiontext,
        		'pluginfile.php',
        		$context->id,
        		'mod_enhancedchoice',
        		$filearea,
        		$optionid);
        $display->options[$optionid]->text = $output;
        $display->options[$optionid]->maxanswer = $choice->maxanswers[$optionid];

        if (array_key_exists($optionid, $allresponses)) {
            $display->options[$optionid]->user = $allresponses[$optionid];
            $totaluser += count($allresponses[$optionid]);
        }
    }
    unset($display->option);
    unset($display->maxanswers);

    $display->numberofuser = $totaluser;
    $context = context_module::instance($cm->id);
    $display->viewresponsecapability = has_capability('mod/enhancedchoice:readresponses', $context);
    $display->deleterepsonsecapability = has_capability('mod/enhancedchoice:deleteresponses',$context);
    $display->fullnamecapability = has_capability('moodle/site:viewfullnames', $context);

    if (empty($allresponses)) {
        echo $OUTPUT->heading(get_string("nousersyet"));
        return false;
    }


    $totalresponsecount = 0;
    foreach ($allresponses as $optionid => $userlist) {
        if ($choice->showunanswered || $optionid) {
            $totalresponsecount += count($userlist);
        }
    }

    $hascapfullnames = has_capability('moodle/site:viewfullnames', $context);

    $viewresponses = has_capability('mod/enhancedchoice:readresponses', $context);
    switch ($forcepublish) {
        case ENHANCEDCHOICE_PUBLISH_NAMES:
            echo '<div id="tablecontainer">';
            if ($viewresponses) {
                echo '<form id="attemptsform" method="post" action="'.$FULLSCRIPT.'" onsubmit="var menu = document.getElementById(\'menuaction\'); return (menu.options[menu.selectedIndex].value == \'delete\' ? \''.addslashes_js(get_string('deleteattemptcheck','quiz')).'\' : true);">';
                echo '<div>';
                echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                echo '<input type="hidden" name="mode" value="overview" />';
            }

            echo "<table cellpadding=\"5\" cellspacing=\"10\" class=\"results names\">";
            echo "<tr>";

            $columncount = array(); // number of votes in each column
            if ($choice->showunanswered) {
                $columncount[0] = 0;
                echo "<th class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'choice');
                echo "</th>";
            }
            $count = 1;
            foreach ($choice->option as $optionid => $optiontext) {
                $columncount[$optionid] = 0; // init counters
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string($optiontext);
                echo "</th>";
                $count++;
            }
            echo "</tr><tr>";

            if ($choice->showunanswered) {
                echo "<td class=\"col$count data\" >";
                // added empty row so that when the next iteration is empty,
                // we do not get <table></table> error from w3c validator
                // MDL-7861
                echo "<table class=\"enhancedchoiceresponse\"><tr><td></td></tr>";
                if (!empty($allresponses[0])) {
                    foreach ($allresponses[0] as $user) {
                        echo "<tr>";
                        echo "<td class=\"picture\">";
                        echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                        echo "</td><td class=\"fullname\">";
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                        echo fullname($user, $hascapfullnames);
                        echo "</a>";
                        echo "</td></tr>";
                    }
                }
                echo "</table></td>";
            }
            $count = 1;
            foreach ($choice->option as $optionid => $optiontext) {
                    echo '<td class="col'.$count.' data" >';

                    // added empty row so that when the next iteration is empty,
                    // we do not get <table></table> error from w3c validator
                    // MDL-7861
                    echo '<table class="enhancedchoiceresponse"><tr><td></td></tr>';
                    if (isset($allresponses[$optionid])) {
                        foreach ($allresponses[$optionid] as $user) {
                            $columncount[$optionid] += 1;
                            echo '<tr><td class="attemptcell">';
                            if ($viewresponses and has_capability('mod/enhancedchoice:deleteresponses',$context)) {
                                echo '<input type="checkbox" name="attemptid[]" value="'. $user->id. '" />';
                            }
                            echo '</td><td class="picture">';
                            echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                            echo '</td><td class="fullname">';
                            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                            echo fullname($user, $hascapfullnames);
                            echo '</a>';
                            echo '</td></tr>';
                       }
                    }
                    $count++;
                    echo '</table></td>';
            }
            echo "</tr><tr>";
            $count = 1;

            if ($choice->showunanswered) {
                echo "<td></td>";
            }

            foreach ($choice->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"col$count count\">";
                if ($choice->limitanswers) {
                    echo get_string("taken", "choice").":";
                    echo $columncount[$optionid];
                    echo "<br/>";
                    echo get_string("limit", "choice").":";
                    echo $choice->maxanswers[$optionid];
                } else {
                    if (isset($columncount[$optionid])) {
                        echo $columncount[$optionid];
                    }
                }
                echo "</td>";
                $count++;
            }
            echo "</tr>";

            /// Print "Select all" etc.
            if ($viewresponses and has_capability('mod/enhancedchoice:deleteresponses',$context)) {
                echo '<tr><td></td><td>';
                echo '<a href="javascript:select_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectall').'</a> / ';
                echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('deselectall').'</a> ';
                echo '&nbsp;&nbsp;';
                echo html_writer::label(get_string('withselected', 'enhancedchoice'), 'menuaction');
                echo html_writer::select(array('delete' => get_string('delete')), 'action', '', array(''=>get_string('withselectedusers')), array('id'=>'menuaction', 'class' => 'autosubmit'));
                $PAGE->requires->yui_module('moodle-core-formautosubmit',
                    'M.core.init_formautosubmit',
                    array(array('selectid' => 'menuaction'))
                );
                echo '<noscript id="noscriptmenuaction" style="display:inline">';
                echo '<div>';
                echo '<input type="submit" value="'.get_string('go').'" /></div></noscript>';
                echo '</td><td></td></tr>';
            }

            echo "</table></div>";
            if ($viewresponses) {
                echo "</form></div>";
            }
            break;
    }
    return $display;
}

/**
 * @global object
 * @param array $attemptids
 * @param object $choice Choice main table row
 * @param object $cm Course-module object
 * @param object $course Course object
 * @return bool
 */
function enhancedchoice_delete_responses($attemptids, $choice, $cm, $course) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }

    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }

    $completion = new completion_info($course);
    foreach($attemptids as $attemptid) {
        if ($todelete = $DB->get_record('enhancedchoice_answers', array('choiceid' => $choice->id, 'userid' => $attemptid))) {
            $DB->delete_records('enhancedchoice_answers', array('choiceid' => $choice->id, 'userid' => $attemptid));
            // Update completion state
            if ($completion->is_enabled($cm) && $choice->completionsubmit) {
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $attemptid);
            }
        }
    }
    return true;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function enhancedchoice_delete_instance($id) {
    global $DB;

    if (! $choice = $DB->get_record("enhancedchoice", array("id"=>"$id"))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("enhancedchoice_answers", array("choiceid"=>"$choice->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("enhancedchoice_options", array("choiceid"=>"$choice->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("enhancedchoice", array("id"=>"$choice->id"))) {
        $result = false;
    }

    return $result;
}

/**
 * Returns text string which is the answer that matches the id
 *
 * @global object
 * @param object $choice
 * @param int $id
 * @return string
 */
function enhancedchoice_get_option_text($choice, $id) {
    global $DB;

    if ($result = $DB->get_record("enhancedchoice_options", array("id" => $id))) {
    	if (! $cm = get_coursemodule_from_instance('enhancedchoice', $choice->id)) {
    		print_error('invalidcoursemodule');
    	}
    	$context = context_module::instance($cm->id);
    	$output = file_rewrite_pluginfile_urls($result->text,
    			'pluginfile.php',
    			 $context->id,
    			'mod_enhancedchoice',
    			'option',
    			$id);
    	
        return $output;
    } else {
        return get_string("notanswered", "enhancedchoice");
    }
}

/**
 * Gets a full choice record
 *
 * @global object
 * @param int $choiceid
 * @return object|bool The choice or false
 */
function enhancedchoice_get_choice($choiceid) {
    global $DB;

    if ($choice = $DB->get_record("enhancedchoice", array("id" => $choiceid))) {
        if ($options = $DB->get_records("enhancedchoice_options", array("choiceid" => $choiceid), "id")) {
            foreach ($options as $option) {
                $choice->option[$option->id] = $option->text;
                $choice->maxanswers[$option->id] = $option->maxanswers;
            }
            return $choice;
        }
    }
    return false;
}

/**
 * @return array
 */
function enhancedchoice_get_view_actions() {
    return array('view','view all','report');
}

/**
 * @return array
 */
function enhancedchoice_get_post_actions() {
    return array('enhancedchoose','choose again');
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the choice.
 *
 * @param object $mform form passed by reference
 */
function enhancedchoice_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'choiceheader', get_string('modulenameplural', 'enhancedchoice'));
    $mform->addElement('advcheckbox', 'reset_choice', get_string('removeresponses','enhancedchoice'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function enhancedchoice_reset_course_form_defaults($course) {
    return array('reset_choice'=>1);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * choice responses for course $data->courseid.
 *
 * @global object
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function enhancedchoice_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'enhancedchoice');
    $status = array();

    if (!empty($data->reset_choice)) {
        $choicessql = "SELECT ch.id
                       FROM {enhancedchoice} ch
                       WHERE ch.course=?";

        $DB->delete_records_select('enhancedchoice_answers', "choiceid IN ($choicessql)", array($data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses', 'enhancedchoice'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('enhancedchoice', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}

/**
 * @global object
 * @global object
 * @global object
 * @uses CONTEXT_MODULE
 * @param object $choice
 * @param object $cm
 * @param int $groupmode
 * @return array
 */
function enhancedchoice_get_response_data($choice, $cm, $groupmode) {
    global $CFG, $USER, $DB;

    $context = context_module::instance($cm->id);

/// Get the current group
    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

/// Initialise the returned array, which is a matrix:  $allresponses[responseid][userid] = responseobject
    $allresponses = array();

/// First get all the users who have access here
/// To start with we assume they are all "unanswered" then move them later
    $allresponses[0] = get_enrolled_users($context, 'mod/enhancedchoice:choose', $currentgroup, user_picture::fields('u', array('idnumber')));

/// Get all the recorded responses for this choice
    $rawresponses = $DB->get_records('enhancedchoice_answers', array('choiceid' => $choice->id));

/// Use the responses to move users into the correct column

    if ($rawresponses) {
        foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group
                $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                unset($allresponses[0][$response->userid]);   // Remove from unanswered column
            }
        }
    }
    return $allresponses;
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function enhancedchoice_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function enhancedchoice_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $choicenode The node to add module settings to
 */
function enhancedchoice_extend_settings_navigation(settings_navigation $settings, navigation_node $choicenode) {
    global $PAGE;

    if (has_capability('mod/enhancedchoice:readresponses', $PAGE->cm->context)) {

        $groupmode = groups_get_activity_groupmode($PAGE->cm);
        if ($groupmode) {
            groups_get_activity_group($PAGE->cm, true);
        }
        // We only actually need the choice id here
        $choice = new stdClass;
        $choice->id = $PAGE->cm->instance;
        $allresponses = enhancedchoice_get_response_data($choice, $PAGE->cm, $groupmode);   // Big function, approx 6 SQL calls per user

        $responsecount =0;
        foreach($allresponses as $optionid => $userlist) {
            if ($optionid) {
                $responsecount += count($userlist);
            }
        }
        $choicenode->add(get_string("viewallresponses", "enhancedchoice", $responsecount), new moodle_url('/mod/enhancedchoice/report.php', array('id'=>$PAGE->cm->id)));
    }
}

/**
 * Obtains the automatic completion state for this choice based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function enhancedchoice_get_completion_state($course, $cm, $userid, $type) {
    global $CFG,$DB;

    // Get choice details
    $choice = $DB->get_record('enhancedchoice', array('id'=>$cm->instance), '*',
            MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if($choice->completionsubmit) {
        return $DB->record_exists('enhancedchoice_answers', array(
                'choiceid'=>$choice->id, 'userid'=>$userid));
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function enhancedchoice_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-enhancedchoice-*'=>get_string('page-mod-enhancedchoice-x', 'enhancedchoice'));
    return $module_pagetype;
}

/**
 * Serves the folder files.
 *
 * @package  mod_enhancedchoice
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function enhancedchoice_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
	global $CFG, $DB;

	if ($context->contextlevel != CONTEXT_MODULE) {
		return false;
	}

	require_course_login($course, true, $cm);
	
	if ($filearea !== 'option') {
		// intro is handled automatically in pluginfile.php
		return false;
	}

	$optionid = array_shift($args); // ignore revision - designed to prevent caching problems only

	$fs = get_file_storage();
	$relativepath = implode('/', $args);
	$fullpath = "/$context->id/mod_enhancedchoice/$filearea/$optionid/$relativepath";
	if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		return false;
	}

	// finally send the file
	// for folder module, we force download file all the time
	send_stored_file($file, 86400, 0, true, $options);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $choice     choice object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.1
 */
function enhancedchoice_view($choice, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
            'context' => $context,
            'objectid' => $choice->id
    );

    $event = \mod_choice\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('choice', $choice);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
