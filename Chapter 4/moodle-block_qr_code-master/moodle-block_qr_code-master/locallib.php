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

require_once("thirdparty/QrCode/src/QrCode.php");
use Endroid\QrCode\QrCode;


/**
 * Generates a new QR code image in the \temp directory
 * 
 * @param $id the id of the block, used to uniquely identify the QR image. 
 *          Each block is only allowed one QR code (although they can be added to the HTML area)
 * @param $data the text to be included in the
 * @return path to the temporary file 
 */

function generate_qrcode($instanceid, $contextid, $data) {
    global $CFG;
    
    $code = new QrCode();
    $code->setText($data);
    $code->setSize(250);
    $code->setPadding(6);
    $code->setErrorCorrection('high');
    $code->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0));
    $code->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
    $code->setLabelFontSize(16);
    
    // generate file path
    $filename = 'qr_code_' . $instanceid . '.png';
    $sourcepath = $CFG->dataroot . '/temp/' . $filename;
    
    $code->render($sourcepath, 'png');
    
    $fs = get_file_storage();
    $file_record = array(
            'contextid'=>$contextid,
            'component'=>'block_qr_code',
            'filearea'=>'qr_code',
            'itemid'=>$instanceid,
            'filepath'=>'/',
            'filename'=>$filename,
            'timecreated'=>time(),
            'timemodified'=>time());
    $result = $fs->create_file_from_pathname($file_record, $sourcepath);
    
    // delete file from temp directory...
    @unlink($sourcepath);
    
    return $result;
}

function qr_code_exists($instanceid, $contextid) {
    $fs = get_file_storage();
    
    $filename = 'qr_code_' . $instanceid . '.png';
    
    $result = $fs->file_exists($contextid, 'block_qr_code', 'qr_code', $instanceid, '/', $filename);
    
    return $result;
}

function get_qrcode_from_filestore($instanceid, $contextid) {
    $fs = get_file_storage();
    
    $filename = 'qr_code_' . $instanceid . '.png';
    
    // Generate file URL
    return moodle_url::make_pluginfile_url($contextid, 'block_qr_code', 'qr_code', $instanceid, '/', $filename);
}

function delete_qrcode_from_filestore($instanceid, $contextid) {
    $fs = get_file_storage();
    
    $filename = 'qr_code_' . $instanceid . '.png';
    
    // Prepare file record object
    $fileinfo = array(
            'component' => 'block_qr_code',
            'filearea' => 'qr_code',     // usually = table name
            'itemid' => $instanceidd,               // usually = ID of row in table
            'contextid' => $contextid, // ID of context
            'filepath' => '/',           // any path beginning and ending in /
            'filename' => $filename); // any filename
    
    // Get file
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    
    // Delete it if it exists
    if ($file) {
        $file->delete();
    }
}

