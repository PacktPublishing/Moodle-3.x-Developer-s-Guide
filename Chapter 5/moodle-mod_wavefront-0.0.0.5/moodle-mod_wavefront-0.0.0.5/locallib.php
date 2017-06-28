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
 * Internal library of functions for module model
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_model
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/lib.php');
require_once("$CFG->libdir/filelib.php");

define('WAVEFRONT_MAX_MODEL_LABEL', 13);
define('WAVEFRONT_MAX_COMMENT_PREVIEW', 20);

/**
 * Checks for ZIP files to unpack.
 *
 * @param context $context
 * @param cm_info $cm
 * @param $model
 * @return void
 */
function wavefront_check_for_zips($context, $cm, $model) {
    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_wavefront', 'model', $model->id, "itemid, filepath, filename", false);
    
    foreach ($files as $storedfile) {
        if ($storedfile->get_mimetype() == 'application/zip') {
            // Unpack.
            $packer = get_file_packer('application/zip');
            $fs->delete_area_files($context->id, 'mod_wavefront', 'unpacktemp', 0);
            $storedfile->extract_to_storage($packer, $context->id, 'mod_wavefront', 'unpacktemp', 0, '/');
            $tempfiles = $fs->get_area_files($context->id, 'mod_wavefront', 'unpacktemp', 0,  "itemid, filepath, filename", false);
            if(count($tempfiles) > 0) {
                $storedfile->delete(); // delete the ZIP file.
                foreach ($tempfiles as $storedfile) {
                    $filename = $storedfile->get_filename();
                    $fileinfo = array(
                            'contextid'     => $context->id,
                            'component'     => 'mod_wavefront',
                            'filearea'      => 'model',
                            'itemid'        => $model->id,
                            'filepath'      => '/',
                            'filename'      => $filename
                    );
                    $storedfile = $fs->create_file_from_storedfile($fileinfo, $storedfile);
                            
                }
            }
            $fs->delete_area_files($context->id, 'mod_wavefront', 'unpacktemp', 0);
            
        } 
    }
}

function wavefront_config_defaults() {
    $defaults = array(
        'disabledplugins' => '',
    );

    $localcfg = get_config('wavefront');

    foreach ($defaults as $name => $value) {
        if (! isset($localcfg->$name)) {
            set_config($name, $value, 'wavefront');
        }
    }
}


/**
 * File browsing support class
 */
class wavefront_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}
