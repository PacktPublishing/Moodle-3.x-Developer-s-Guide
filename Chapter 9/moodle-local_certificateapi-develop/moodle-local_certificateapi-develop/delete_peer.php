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
 * Page to allow the administrator to configure API hosts, and add new ones
 *
 * @package    certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id     = required_param('id', PARAM_INT);              // course id
$delete = optional_param('delete', '', PARAM_ALPHANUM); // delete confirmation hash

$PAGE->set_url('/local/certificateapi/delete_peer.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
require_login();

$strdeletehost = get_string("deletehost", "local_certificateapi");
    
if (! $host = $DB->get_record("certificateapi_host", array("id"=>$id))) {
    print_error("invalidhostid", 'local_certificateapi', '', $id);
}

$host = $DB->get_record("certificateapi_host", array("id"=>$id));
$fullname = format_string($host->fullname, true);
     
if (! $delete) {
    $strdeletecheck = get_string("deletecheck", "", $fullname);
    $strdeletehostcheck = get_string("deletehostcheck", "local_certificateapi");

    $PAGE->navbar->add($strdeletecheck);
    $PAGE->set_title("$host->fullname: $strdeletecheck");
    $PAGE->set_heading($host->fullname);
    echo $OUTPUT->header();

    $message = "$strdeletehostcheck<br /><br />" . format_string($host->fullname, true);
    echo $OUTPUT->confirm($message, "delete_peer.php?id=$host->id&delete=".md5($host->last_connect_time), "index.php");

    echo $OUTPUT->footer();
    exit;
}

if ($delete != md5($host->last_connect_time)) {
    print_error("invalidmd5");
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

// OK checks done, delete the host now.
$strdeletinghost = get_string("deletinghost", "local_certificateapi", $host->fullname);

$PAGE->navbar->add($strdeletinghost);
$PAGE->set_title("$host->fullname: $strdeletinghost");
$PAGE->set_heading($host->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strdeletinghost);

$DB->delete_records("certificateapi_host", array("id"=>$host->id));
    
echo $OUTPUT->heading( get_string("deletedhost", "local_certificateapi", $host->fullname) );

echo $OUTPUT->continue_button("index.php");

echo $OUTPUT->footer();


