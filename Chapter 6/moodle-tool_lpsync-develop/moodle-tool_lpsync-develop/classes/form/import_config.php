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
 * This file contains the form add/update a competency framework.
 *
 * @package   tool_lpsync
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lpsync\form;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

use moodleform;
use core_competency\api;
use core_text;
use csv_import_reader;

require_once($CFG->libdir.'/formslib.php');

/**
 * Import Competency framework database and mapping form.
 *
 * @package   tool_lpsync
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_config extends moodleform {

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        global $CFG;
        
        $mform = $this->_form;
        
        $config = $this->_customdata;
        
        // External database settings
        $mform->addElement('header', 'extdb', get_string('db_header', 'tool_lpsync'));
        
        $dbtypes = array("access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative", "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
        foreach ($dbtypes as $dbtype) {
            $dboptions[$dbtype] = $dbtype;
        }
        $mform->addElement('select', 'type', get_string('db_type', 'tool_lpsync'), $dboptions);
        $mform->addRule('type', null, 'required');
        $mform->setDefault('type', $config['type']);
        
        $mform->addElement('text', 'host', get_string('db_host', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('host', PARAM_HOST);
        $mform->addRule('host', null, 'required');
        $mform->setDefault('host', $config['host']);
        
        $mform->addElement('text', 'user', get_string('db_user', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('user', PARAM_NOTAGS);
        $mform->setDefault('user', $config['user']);
        
        $mform->addElement('password', 'pass', get_string('db_pass', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('pass', PARAM_NOTAGS);
        $mform->setDefault('pass', $config['pass']);
        
        $mform->addElement('text', 'name', get_string('db_name', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', null, 'required');
        $mform->setDefault('name', $config['name']);
        
        $mform->addElement('text', 'table', get_string('db_table', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('table', PARAM_NOTAGS);
        $mform->addRule('table', null, 'required');
        $mform->setDefault('table', $config['table']);
        
        // Field mapping settings
        $mform->addElement('header', 'mappings', get_string('mappings_header', 'tool_lpsync'));
        
        $mform->addElement('text', 'parentidnumber', get_string('parentidnumber', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('parentidnumber', PARAM_NOTAGS);
        $mform->addRule('parentidnumber', null, 'required');
        $mform->setDefault('parentidnumber', $config['parentidnumber']);
        $mform->addHelpButton('parentidnumber', 'parentidnumber', 'tool_lpsync');
        
        $mform->addElement('text', 'idnumber', get_string('idnumber', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('idnumber', PARAM_NOTAGS);
        $mform->addRule('idnumber', null, 'required');
        $mform->setDefault('idnumber', $config['idnumber']);
        $mform->addHelpButton('idnumber', 'idnumber', 'tool_lpsync');
        
        $mform->addElement('text', 'shortname', get_string('shortname', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', null, 'required');
        $mform->setDefault('shortname', $config['shortname']);
        $mform->addHelpButton('shortname', 'shortname', 'tool_lpsync');
        
        $mform->addElement('text', 'description', get_string('description', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('description', PARAM_NOTAGS);
        $mform->addRule('description', null, 'required');
        $mform->setDefault('description', $config['description']);
        $mform->addHelpButton('description', 'description', 'tool_lpsync');
        
        $mform->addElement('text', 'descriptionformat', get_string('descriptionformat', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('descriptionformat', PARAM_NOTAGS);
        $mform->addRule('descriptionformat', null, 'required');
        $mform->setDefault('descriptionformat', $config['descriptionformat']);
        $mform->addHelpButton('descriptionformat', 'descriptionformat', 'tool_lpsync');
        
        $mform->addElement('text', 'scalevalues', get_string('scalevalues', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('scalevalues', PARAM_NOTAGS);
        $mform->addRule('scalevalues', null, 'required');
        $mform->setDefault('scalevalues', $config['scalevalues']);
        $mform->addHelpButton('scalevalues', 'scalevalues', 'tool_lpsync');
        
        $mform->addElement('text', 'scaleconfiguration', get_string('scaleconfiguration', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('scaleconfiguration', PARAM_NOTAGS);
        $mform->addRule('scaleconfiguration', null, 'required');
        $mform->setDefault('scaleconfiguration', $config['scaleconfiguration']);
        $mform->addHelpButton('scaleconfiguration', 'scaleconfiguration', 'tool_lpsync');
        
        $mform->addElement('text', 'ruletype', get_string('ruletype', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('ruletype', PARAM_NOTAGS);
        $mform->setDefault('ruletype', $config['ruletype']);

        $mform->addElement('text', 'ruleoutcome', get_string('ruleoutcome', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('ruleoutcome', PARAM_NOTAGS);
        $mform->setDefault('ruleoutcome', $config['ruleoutcome']);

        $mform->addElement('text', 'ruleconfig', get_string('ruleconfig', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('ruleconfig', PARAM_NOTAGS);
        $mform->setDefault('ruleconfig', $config['ruleconfig']);
        
        $mform->addElement('text', 'relatedidnumbers', get_string('relatedidnumbers', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('relatedidnumbers', PARAM_NOTAGS);
        $mform->setDefault('relatedidnumbers', $config['relatedidnumbers']);
        $mform->addHelpButton('relatedidnumbers', 'relatedidnumbers', 'tool_lpsync');
        
        $mform->addElement('text', 'isframework', get_string('isframework', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('isframework', PARAM_NOTAGS);
        $mform->addRule('isframework', null, 'required');
        $mform->setDefault('isframework', $config['isframework']);
        $mform->addHelpButton('isframework', 'isframework', 'tool_lpsync');
        
        $mform->addElement('text', 'taxonomy', get_string('taxonomy', 'tool_lpsync'), array('size'=>'48'));
        $mform->setType('taxonomy', PARAM_NOTAGS);
        $mform->addRule('taxonomy', null, 'required');
        $mform->setDefault('taxonomy', $config['taxonomy']);
        $mform->addHelpButton('taxonomy', 'taxonomy', 'tool_lpsync');
        
        $this->add_action_buttons(true);
    }
}
