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
 * Settings for the course_report block
 *
 * @copyright 2017 Ian Wiild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_course_report
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_course_report_allowcssclasses', get_string('allowadditionalcssclasses', 'block_course_report'),
                       get_string('configallowadditionalcssclasses', 'block_course_report'), 0));
    
    // Starting time of log queries.
    $options = array(
    		'sincestart' => get_string('sincestart', 'block_course_report'),
    		'sinceforever' => get_string('sinceforever', 'block_course_report'),
    );
    $settings->add(new admin_setting_configselect('block_course_report/activitysince',
    		get_string('checkforactivity', 'block_course_report'),
    		get_string('checkforactivity_help', 'block_course_report'),
    		'sincestart',
    		$options)
    		);
}


