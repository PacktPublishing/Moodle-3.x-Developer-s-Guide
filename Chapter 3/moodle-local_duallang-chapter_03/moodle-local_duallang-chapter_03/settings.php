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
 * Local plugin "duallang" - Settings
*
* @package    local_duallang
* @copyright  2016 Ian Wild
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // New settings page
    $settings = new admin_settingpage('local_duallang', get_string('local_duallang', 'local_duallang'));
    
    $settings->add(new admin_setting_heading('local_duallang/generalheading', get_string('generalheading', 'local_duallang'), ''));
    
    $settings->add(new admin_setting_configcheckbox('local_duallang/enabled', get_string('enableduallangs', 'local_duallang')));

    // obtain list of available languages from the language manager

    $settings->add(new admin_setting_heading('local_duallang/languageheading', get_string('languageheading', 'local_duallang'), ''));
    
    $languages = get_string_manager()->get_list_of_translations();
    
    $currentlang = current_language();

    // Primary language
    $settings->add(new admin_setting_configselect('local_duallang/primarylanguage', get_string('primarylang', 'local_duallang'), get_string('primarylang_desc', 'local_duallang'), $currentlang, $languages));
    // Secondary language
    $settings->add(new admin_setting_configselect('local_duallang/secondarylanguage', get_string('secondarylang', 'local_duallang'), get_string('secondarylang_desc', 'local_duallang'), $currentlang, $languages));
    
    // Reading order
    $readingorder = array('LTR' => get_string('lefttoright', 'local_duallang'), 'RTL' => get_string('righttoleft', 'local_duallang'));
    $settings->add(new admin_setting_configselect('local_duallang/readingorder', get_string('readingorder', 'local_duallang'), get_string('readingorder_desc', 'local_duallang'), 'LTR', $readingorder));
    
    // Add settings page to navigation tree
    $ADMIN->add('localplugins', $settings);
}
