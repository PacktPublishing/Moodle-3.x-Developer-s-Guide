<?php
// This file is part of the Local welcome plugin
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
 * This plugin provides an external web services API which reports on 
 * certificate completions within a given time window.
 *
 * @package    local
 * @subpackage certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $moderator = get_admin();
    $site = get_site();

    $settings = new admin_settingpage('local_certificateapi', get_string('pluginname', 'local_certificateapi'));
    $ADMIN->add('localplugins', $settings);

    $availablefields = new moodle_url('/local/certificateapi/index.php');

    $name = 'local_certificateapi/enabled';
    $title = get_string('enabled', 'local_certificateapi');
    $description = get_string('api_enabled_desc', 'local_certificateapi', $availablefields->out());
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $settings->add($setting);
}

