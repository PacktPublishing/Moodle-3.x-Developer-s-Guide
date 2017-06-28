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
 * course_report library
 *
 * @package    block_course_report
 * @author     Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */

defined('MOODLE_INTERNAL') || die();

define('CR_SUCCESS', 0);
define('CR_COURSE_NOT_STARTED', 1);
define('CR_NO_LOG_READER_ENABLED', 2);
define('CR_NO_LOG_ENTRIES', 3);

function course_report_get_views($courseid) {
	global $DB;
	
	$course = get_course($courseid);
	
	// set an error flag - assume success until we decide otherwise
	$error = CR_SUCCESS;
	
	// Get global settings.
	$activitysince = get_config('block_course_report', 'activitysince');
	if ($activitysince === false) {
		$activitysince = 'sincestart';
	}
	
	$now = time();
	
	// Check that the course has started.
	if ($activitysince == 'sincestart' && $now < $course->startdate) {
		$error = CR_COURSE_NOT_STARTED;
		$arguments = array(
				$error,
		);
		
		return $arguments;
	}
	
	$useinternalreader = false; // Flag to determine if we should use the internal reader.
	$uselegacyreader = false; // Flag to determine if we should use the legacy reader.
		
	// Get list of readers.
	$logmanager = get_log_manager();
	$readers = $logmanager->get_readers();
		
	// Get preferred reader.
	if (!empty($readers)) {
		foreach ($readers as $readerpluginname => $reader) {
			
			// If sql_internal_table_reader is preferred reader.
			if ($reader instanceof \core\log\sql_internal_table_reader) {
				$useinternalreader = true;
				$logtable = $reader->get_internal_log_table_name();
			}
			
			// If legacy reader is preferred reader.
			if ($readerpluginname == 'logstore_legacy') {
				$uselegacyreader = true;
			}
		}
	}
	
	// If no legacy and no internal log then don't proceed.
	if (!$uselegacyreader && !$useinternalreader) {
		$error = CR_NO_LOG_READER_ENABLED;
		$arguments = array(
				$error,
		);
			
		return $arguments;
	}
		
	// Get record from sql_internal_table_reader.
	if ($useinternalreader) {
		$timesince = ($activitysince == 'sincestart') ? 'AND timecreated >= :coursestart' : '';
		$sql = "SELECT contextinstanceid as cmid, COUNT('x') AS numviews, COUNT(DISTINCT userid) AS distinctusers
                      FROM {" . $logtable . "} l
                      WHERE courseid = :courseid
                      $timesince
                      AND anonymous = 0
                      AND crud = 'r'
                      AND contextlevel = :contextmodule
                      GROUP BY contextinstanceid";
                      $params = array('courseid' => $course->id, 'contextmodule' => CONTEXT_MODULE, 'coursestart' => $course->startdate);
                      $views = $DB->get_records_sql($sql, $params);
                          
	} else if ($uselegacyreader) {
		// If using legacy log then get activity usage from old table.
		$logactionlike = $DB->sql_like('l.action', ':action');
		$timesince = ($activitysince == 'sincestart') ? 'AND l.time >= :coursestart' : '';
		$sql = "SELECT cm.id, COUNT('x') AS numviews, COUNT(DISTINCT userid) AS distinctusers
		FROM {course_modules} cm
		JOIN {modules} m
		ON m.id = cm.module
		JOIN {log} l
		ON l.cmid = cm.id
		WHERE cm.course = :courseid
		$timesince
		AND $logactionlike
		AND m.visible = 1
		GROUP BY cm.id";
		$params = array('courseid' => $course->id, 'action' => 'view%', 'coursestart' => $course->startdate);
		if (!empty($minloginternalreader)) {
			$params['timeto'] = $minloginternalreader;
		}
		$views = $DB->get_records_sql($sql, $params);
	}
	
	// Check that there were some results.
	if (empty($views)) {
		$error = CR_NO_LOG_ENTRIES;
		$arguments = array(
				$error,
		);
		
		return $arguments;
	}
	
	// Get the min, max and totals.
	$firstactivity = array_shift($views);
	$totalviews = $firstactivity->numviews;
	$totalusers = $firstactivity->distinctusers;
	$minviews = $firstactivity->numviews;
	$maxviews = $firstactivity->numviews;
	foreach ($views as $key => $activity) {
		
		$totalviews += $activity->numviews;
		if ($activity->numviews < $minviews) {
			$minviews = $activity->numviews;
		}
		if ($activity->numviews > $maxviews) {
			$maxviews = $activity->numviews;
		}
		$totalusers += $activity->distinctusers;
	}
	array_unshift($views, $firstactivity);
	
	foreach ($views as $key => $activity) {
		
		if($cm = $DB->get_record('course_modules', array('id' => $activity->cmid))) {
			$modname = $DB->get_field('modules', 'name', array('id' => $cm->module));
			if ($name = $DB->get_field("$modname", 'name', array('id' => $cm->instance))) {
				$activity->name = $name;
			}
		} else {
			$activity->name = get_string('unknownmod', 'block_course_report');
		}
	}
	
	$arguments = array(
			$error,
			json_encode($views),
			$minviews,
			$maxviews
	);
	
	return $arguments;
}