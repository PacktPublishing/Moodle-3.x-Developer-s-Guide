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
 * Form for editing QR code block instances.
 *
 * @package   block_qr_code
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("locallib.php");

require_once("thirdparty/QrCode/src/QrCode.php");
use Endroid\QrCode\QrCode;

class block_qr_code extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_qr_code');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newqrcodeblock', 'block_qr_code'));
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        // have we loaded the block's content already?
        if ($this->content !== NULL) {
            return $this->content;
        }
        
        // have we created a new QR code image?
        if(isset($this->config->data)) {
            if(!qr_code_exists($this->instance->id, $this->context->id)) {
                generate_qrcode($this->instance->id, $this->context->id, $this->config->data);
            }
        }
        
        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->description)) {
            // rewrite url
            $this->config->description = file_rewrite_pluginfile_urls($this->config->description, 'pluginfile.php', $this->context->id, 'block_qr_code', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = format_text($this->config->description, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        if(qr_code_exists($this->instance->id, $this->context->id)) {
            // Send the QR code from the file store
            $this->content->text .= 
            
            '<div class="qr_code"><img src="'.get_qrcode_from_filestore($this->instance->id, $this->context->id).'"/></div>'; 
        }
        
        unset($filteropt); // memory footprint

        return $this->content;
    }


    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->description = file_save_draft_area_files($data->description['itemid'], $this->context->id, 'block_qr_code', 'content', 0, array('subdirs'=>true), $data->description['text']);
        $config->format = $data->description['format'];

        // clear out any QR codes created previously for a lazy init...
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_qr_code', 'qr_code', $this->instance->id);
        
        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_qr_code', 'qr_code', $this->instance->id);
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();
        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_qr_code', 'content', 0, false)) {
            $draftitemid = 0;
            file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_qr_code', 'content', 0, array('subdirs' => true));
            file_save_draft_area_files($draftitemid, $this->context->id, 'block_qr_code', 'content', 0, array('subdirs' => true));
        }
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }

    /*
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_qr_code_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' '.$this->config->classes;
            }
        }

        return $attributes;
    }
}
