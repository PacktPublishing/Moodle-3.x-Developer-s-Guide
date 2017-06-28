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
 * Strings for component 'tool_lpsync', language 'en'
 *
 * @package    tool_lpsync
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Competency framework sync';
$string['importfile'] = 'CSV framework description file';
$string['invalidimportdata'] = 'Data format is invalid.';
$string['noframeworks'] = 'No competency frameworks have been created yet';
$string['import'] = 'Import';

$string['db_header'] = 'External database configuration';
$string['db_host'] = 'Host';
$string['db_type'] = 'Type';
$string['db_user'] = 'Username';
$string['db_pass'] = 'Password';
$string['db_name'] = 'Database name';
$string['db_table'] = 'Table';

$string['mappings_header'] = 'Field mappings';
$string['parentidnumber'] = 'Parent id number';
$string['idnumber'] = 'Id number';
$string['shortname'] = 'Shortname';
$string['description'] = 'Description';
$string['descriptionformat'] = 'Description format';
$string['scalevalues'] = 'Scale values';
$string['scaleconfiguration'] = 'Scale configuration';
$string['ruletype'] = 'Rule type (optional)';
$string['ruleoutcome'] = 'Rule outcome (optional)';
$string['ruleconfig'] = 'Rule config (optional)';
$string['isframework'] = 'Is framework';
$string['taxonomy'] = 'Taxonomy';
$string['competencyscale'] = 'Competency Scale: {$a}';
$string['exportid'] = 'Exported id (optional)';
$string['competencyscaledescription'] = 'Competency scale created by import';
$string['relatedidnumbers'] = 'Cross referenced competency id numbers (optional)';
$string['confirm'] = 'Confirm';
$string['confirmcolumnmappings'] = 'Confirm the columns mappings';
$string['csvdelimiter'] = 'CSV delimiter';
$string['csvdelimiter_help'] = 'CSV delimiter of the CSV file.';
$string['encoding'] = 'Encoding';
$string['encoding_help'] = 'Encoding of the CSV file.';
// configuration form help strings
$string['parentidnumber_help'] = 'Competencies are arranged in a tree. This field indicates which competency is the parent of the current row.';
$string['idnumber_help'] = 'A string that uniquely identifies this competency in this framework';
$string['shortname_help'] = 'A short name for the competency';
$string['description_help'] = 'A longer description for the competency';
$string['descriptionformat_help'] = 'A number representing the format of the text in the description. Valid options are: 0 = Moodle format, 1 = HTML, 2 = Plain text, 3 = Wiki format, 4 = Markdown';
$string['scalevalues_help'] = 'Only required on the framework row. Defines a comma separated list of options used to grade the competencies';
$string['scaleconfiguration_help'] = 'A json encoded object used to define the scale configuration.';
$string['relatedidnumbers_help'] = 'A comma separated list of id numbers belonging to competencies related to the current row';
$string['isframework_help'] = 'Must be 1 for a single row in the framework which defines the name and description of the entire framework';
$string['taxonomy_help'] = 'Defined for the framework row, defines the lang string keys used to describe competencies at each level of the framework';

