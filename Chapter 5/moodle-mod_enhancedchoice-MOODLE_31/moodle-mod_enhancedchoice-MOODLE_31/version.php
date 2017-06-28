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
 * Version information
 *
 * @package    mod
 * @subpackage enhancedchoice
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}, Ian David Wild {@link http://heavy-horse.co.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017022801;       // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2016052300;    // Requires this Moodle version (3.1)
$plugin->component = 'mod_enhancedchoice';     // Full name of the plugin (used for diagnostics)
$plugin->maturity  = MATURITY_ALPHA;	// How stable the plugin is
$plugin->release   = '0.3 (Build: 2017022500)';  // Human-readable version name
$plugin->cron      = 0;
