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
 * Wavefront module renderer
 *
 * @package   mod_wavefront
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_wavefront_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the Wavefront model
     * @param object $wavefront The wavefront activity with which the model is associated
     * @param boolean $editing true if the current user can edit the model, else false.
     */
    public function display_model($wavefront, $editing = false, $modelerr=false) {
        global $DB;
        
        $output = '';
        
        if ($wavefront->intro && !$editing) {
            $output .= $this->output->box(format_module_intro('wavefront', $wavefront, $this->page->cm->id), 'generalbox', 'intro');
        }
        
        $output .= $this->output->box_start('generalbox wavefront clearfix');
        
        
        $model = $DB->get_record('wavefront_model', array('wavefrontid' => $wavefront->id));
        
        if(!$model || $modelerr) {
            $output .= $this->output->heading(get_string("errornomodel", "wavefront"));
        } else {
            $output .= '<div class="wavefront-model-container">'.
                    '<div class="wavefront-model-wrapper">'.
                    '<div class="wavefront-model-frame">';
            $posclass = ($model->descriptionpos == 1) ? 'top' : 'bottom'; // doesn't matter if it's hidden
            $captiondiv = html_writer::tag('div', format_text($model->description, FORMAT_MOODLE), array('class' => "wavefront-model-caption $posclass"));
            
            if($model->descriptionpos == 1) {
                $output .= $captiondiv;
            }
            
            $output .= '<div id="wavefront_stage"></div>';
             
            if($model->descriptionpos == 0) {
                $output .= $captiondiv;
            }
            
            $output .= '</div></div></div>';
        }
        
        // Add in edit link if necessary
        // TODO We are not passing the wavefront model id for now - we may display more than one model on the page.
        if ($editing) {
            $url = new moodle_url('/mod/wavefront/edit_model.php');
            $output .= '<form action="'. $url . '">'.
                    '<input type="hidden" name="id" value="'. $wavefront->id .'" />'.
                    '<input type="hidden" name="cmid" value="'.$this->page->cm->id.'" />'.
                    '<input type="hidden" name="page" value="0" />'.
                    '<input type="submit" Value="'.get_string('editmodel', 'wavefront').'" />'.
                    '</form>';
        }
        
        $output .= $this->output->box_end();
        
        return $output;
    }
    
    /**
     * Output the HTML for a comment in the given context.
     * @param object $comment The comment record to output
     * @param object $context The context from which this is being displayed
     */
    private function print_comment($comment, $context) {
        global $DB, $CFG, $COURSE;
    
        $output = '';
        
        $user = $DB->get_record('user', array('id' => $comment->userid));
    
        $deleteurl = new moodle_url('/mod/wavefront/comment.php', array('id' => $comment->wavefrontid, 'delete' => $comment->id));
    
        $output .= '<table cellspacing="0" width="50%" class="boxaligncenter datacomment forumpost">'.
                '<tr class="header"><td class="picture left">'.$this->output->user_picture($user, array('courseid' => $COURSE->id)).'</td>'.
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
        
        return $output;
    }

    public function display_comments($wavefront, $editing = false) {
        global $DB;
        
        $output = '';
        
        $options = array();
        
        $context = context_module::instance($this->page->cm->id);
        
        if ($wavefront->comments && has_capability('mod/wavefront:addcomment', $context)) {
            $opturl = new moodle_url('/mod/wavefront/comment.php', array('id' => $wavefront->id));
            $options[] = html_writer::link($opturl, get_string('addcomment', 'wavefront'));
        }
        
        if (count($options) > 0) {
            $output .= $this->output->box(implode(' | ', $options), 'center');
        }
        
        if (!$editing && $wavefront->comments && has_capability('mod/wavefront:viewcomments', $context)) {
            if ($comments = $DB->get_records('wavefront_comments', array('wavefrontid' => $wavefront->id), 'timemodified ASC')) {
                foreach ($comments as $comment) {
                    $output .= $this->print_comment($comment, $context);
                }
            }
        }

        return $output;
    }

}