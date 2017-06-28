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


namespace tool_lpsync;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/adodb/adodb.inc.php');

use core_competency\api;
use grade_scale;
use stdClass;
use context_system;
use csv_import_reader;

/**
 * Import Competency framework form.
 *
 * @package   tool_lpsync
 * @copyright 2017 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class framework_importer {

    /** @var string $error The errors message from reading the xml */
    var $error = '';

    /** @var array $flat The flat competencies tree */
    var $flat = array();
    /** @var array $framework The framework info */
    var $framework = array();
    var $mappings = array();
    var $importid = 0;
    var $foundheaders = array();

    /** @var config */
    var $config = array();
    
    /**
     * Returns true if the database is configured, else returns false
     * 
     * @return bool
     */
    function is_configured() {
        $result = true;
        
        if(count($this->config) == 0) {
            $result = false;
        }
     
        return $result;
    }
    
    /**
     * Loads up settings and determines if anything has been configured or not. If there is no configuration data at all then
     * it populates its settings table with its defaults.
     * 
     * @return bool
     */
    function init() {
        global $DB;
        
        // Get details of external database from our config. Currently we do this one record at a time, which is a little clunky:
        $records = $DB->get_records('tool_lpsync', null, null);
    
        // if there aren't any entries in the table then we need to prepare them:
        if(count($records) == 0) {
            
            $rows = array(  'type' => 'mysqli', 
                            'host' => 'localhost', 
                            'user' => '',
                            'pass' => '',
                            'name' => '',
                            'table' => '',
                            
                            'parentidnumber' => 'parentidnumber',
                            'idnumber' => 'idnumber',
                            'shortname' => 'shortname', 
                            'description' => 'description',
                            'descriptionformat' => 'descriptionformat',
                            'scalevalues' => 'scalevalues',
                            'scaleconfiguration' => 'scaleconfiguration',
                    
                            'ruletype' => '',
                            'ruleoutcome' => '',
                            'ruleconfig' => '',
                    
                            'relatedidnumbers' => 'relatedidnumbers',
                            'isframework' => 'isframework',
                            'taxonomy' => 'taxonomy'
                    );
            
            foreach($rows as $name => $value) {
                 $object = new stdClass();
                 $object->name = $name;
                 $object->value = $value;
                 $DB->insert_record('tool_lpsync', $object);
            }
            
            // try that again
            $records = $DB->get_records('tool_lpsync');    
        }
        
        foreach($records as $record) {
            $this->config[$record->name] = $record->value;
        }
        
        return true;
    }
    
    /**
     * Stores importer configuration
     * 
     * @param stdClass $data - form data
     */
    public function update_config($data) {
        global $DB;
        
        // what settings do we need to store?
        $settings = array('type', 'host', 'user', 'pass', 'name', 'table', 'parentidnumber', 
                'idnumber', 'shortname', 'description', 'descriptionformat', 'scalevalues', 'scaleconfiguration',
                'ruletype', 'ruleoutcome', 'ruleconfig', 'relatedidnumbers', 'isframework', 'taxonomy');
        
        foreach($settings as $setting) {
            // only update a current record    
            $sql = 'UPDATE {tool_lpsync} SET `value` = ? WHERE `name` = ?';
            $params = array($data->{$setting}, $setting);
            $DB->execute($sql, $params);
        }
        
        // re-initialise with the new settings
        $this->init();
    }
    
    /**
     * Connect to external database.
     *
     * @return ADOConnection
     * @throws moodle_exception
     */
    function db_connect() {
        global $CFG;
        
        if ($this->is_configured() === false) {
            throw new moodle_exception('dbcantconnect', 'tool_lpsync');
        }
        
        // Connect to the external database (forcing new connection).
        $authdb = ADONewConnection($this->config['type']);
        if (!empty($CFG->debuglpsync)) {
            $authdb->debug = true;
        }
        
        $authdb->Connect($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['name'], true);
        $authdb->SetFetchMode(ADODB_FETCH_ASSOC);
        
        return $authdb;
    }
    
    public function fail($msg) {
        $this->error = $msg;
        return false;
    }

    public function get_importid() {
        return $this->importid;
    }

    public static function list_required_headers() {
        return array(
            get_string('parentidnumber', 'tool_lpsync'),
            get_string('idnumber', 'tool_lpsync'),
            get_string('shortname', 'tool_lpsync'),
            get_string('description', 'tool_lpsync'),
            get_string('descriptionformat', 'tool_lpsync'),
            get_string('scalevalues', 'tool_lpsync'),
            get_string('scaleconfiguration', 'tool_lpsync'),
            get_string('ruletype', 'tool_lpsync'),
            get_string('ruleoutcome', 'tool_lpsync'),
            get_string('ruleconfig', 'tool_lpsync'),
            get_string('relatedidnumbers', 'tool_lpsync'),
            get_string('isframework', 'tool_lpsync'),
            get_string('taxonomy', 'tool_lpsync'),
        );
    }

    public function list_found_headers() {
        return $this->foundheaders;
    }

    private function read_mapping_data() {
        if ($this->config) {
            return array(
                'parentidnumber' => $this->config['parentidnumber'],   
                'idnumber' => $this->config['idnumber'],
                'shortname' => $this->config['shortname'],
                'description' => $this->config['description'],
                'descriptionformat' => $this->config['descriptionformat'],
                'scalevalues' => $this->config['scalevalues'],
                'scaleconfiguration' => $this->config['scaleconfiguration'],
                'ruletype' => $this->config['ruletype'],
                'ruleoutcome' => $this->config['ruleoutcome'],
                'ruleconfig' => $this->config['ruleconfig'],
                'relatedidnumbers' => $this->config['relatedidnumbers'],
                'isframework' => $this->config['isframework'],
                'taxonomy' => $this->config['taxonomy']
            );
        }
    }

    /**
     * Parse - takes loaded competency data and stores in Moodle.
     */
    public function synchronize($text = null, $encoding = null, $delimiter = null, $importid = 0, $mappingdata = null) {
        global $CFG;
        
        // The format of our records is:
        // Parent ID number, ID number, Shortname, Description, Description format, Scale values, Scale configuration, Rule type, Rule outcome, Rule config, Is framework, Taxonomy
        
        $authdb = $this->db_connect();
        
        if($authdb) {
            // get all rows as a two dimentional array
            $table = $this->config['table'];
            $sql = "SELECT * FROM $table WHERE 1=1";
            $rs = $authdb->Execute($sql);
            
            if($rs) {
                $rows = $rs->GetRows();
                
                $rs->Close();
                
                $domainid = 1;
                
                $flat = array();
                $framework = null;
                
                $mapping = $this->read_mapping_data();
                
                foreach($rows as $row) {
                    $parentidnumber = $row[$mapping['parentidnumber']];
                    $idnumber = $row[$mapping['idnumber']];
                    $shortname = $row[$mapping['shortname']];
                    $description = $row[$mapping['description']];
                    $descriptionformat = $row[$mapping['descriptionformat']];
                    $scalevalues = $row[$mapping['scalevalues']];
                    $scaleconfiguration = $row[$mapping['scaleconfiguration']];
                    $ruletype = $row[$mapping['ruletype']];
                    $ruleoutcome = $row[$mapping['ruleoutcome']];
                    $ruleconfig = $row[$mapping['ruleconfig']];
                    $relatedidnumbers = $row[$mapping['relatedidnumbers']];
                    $isframework = $row[$mapping['isframework']];
                    $taxonomies = $row[$mapping['taxonomy']];
                    
                    if ($isframework) {
                        $framework = new stdClass();
                        $framework->idnumber = shorten_text(clean_param($idnumber, PARAM_TEXT), 100);
                        $framework->shortname = shorten_text(clean_param($shortname, PARAM_TEXT), 100);
                        $framework->description = clean_param($description, PARAM_RAW);
                        $framework->descriptionformat = clean_param($descriptionformat, PARAM_INT);
                        $framework->scalevalues = $scalevalues;
                        $framework->scaleconfiguration = $scaleconfiguration;
                        $framework->taxonomies = $taxonomies;
                        $framework->children = array();
                    } else {
                        $competency = new stdClass();
                        $competency->parentidnumber = clean_param($parentidnumber, PARAM_TEXT);
                        $competency->idnumber = shorten_text(clean_param($idnumber, PARAM_TEXT), 100);
                        $competency->shortname = shorten_text(clean_param($shortname, PARAM_TEXT), 100);
                        $competency->description = clean_param($description, PARAM_RAW);
                        $competency->descriptionformat = clean_param($descriptionformat, PARAM_INT);
                        $competency->ruletype = $ruletype;
                        $competency->ruleoutcome = clean_param($ruleoutcome, PARAM_INT);
                        $competency->ruleconfig = $ruleconfig;
                        $competency->relatedidnumbers = $relatedidnumbers;
                        $competency->scalevalues = $scalevalues;
                        $competency->scaleconfiguration = $scaleconfiguration;
                        $competency->children = array();
                        $flat[$idnumber] = $competency;
                    }
                }
                
                $this->flat = $flat;
                $this->framework = $framework;
                
                if ($this->framework == null) {
                    $this->fail(get_string('invalidimportdata', 'tool_lpsync'));
                    return;
                } else {
                    // Build a tree from this flat list.
                    $this->add_children($this->framework, '');
                }
            }
            
            $authdb->Close();
        }
    }
    
    /**
     * Constructor - initialise this instance.
     */
    public function __construct() {
        $this->init();        
    }

    /**
     * Recursive function to build a tree from the flat list of nodes.
     */
    public function add_children(& $node, $parentidnumber) {
        foreach ($this->flat as $competency) {
            if ($competency->parentidnumber == $parentidnumber) {
                $node->children[] = $competency;
                $this->add_children($competency, $competency->idnumber);
            } 
        }
    }

    /**
     * @return array of errors from parsing the xml.
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Recursive function to add a competency with all it's children.
     */
    public function create_competency($record, $parent, $framework) {
        $competency = new stdClass();
        $competency->competencyframeworkid = $framework->get_id();
        $competency->shortname = $record->shortname;
        if (!empty($record->description)) {
            $competency->description = $record->description;
            $competency->descriptionformat = $record->descriptionformat;
        }
        if ($record->scalevalues) {
            $competency->scaleid = $this->get_scale_id($record->scalevalues, $competency->shortname);
            $competency->scaleconfiguration = $this->get_scale_configuration($competency->scaleid, $record->scaleconfiguration);
        }
        if ($parent) {
            $competency->parentid = $parent->get_id();
        } else {
            $competency->parentid = 0;
        }
        $competency->idnumber = $record->idnumber;

        if (!empty($competency->idnumber) && !empty($competency->shortname)) {
            $comp = api::create_competency($competency);
            $record->createdcomp = $comp;
            foreach ($record->children as $child) {
                $this->create_competency($child, $comp, $framework);
            }

            return $comp;
        }
        return false;
    }

    /**
     * Recreate the scale config to point to a new scaleid.
     */
    public function get_scale_configuration($scaleid, $config) {
        $asarray = json_decode($config);
        $asarray[0]->scaleid = $scaleid;
        return json_encode($asarray);
    }

    /**
     * Search for a global scale that matches this set of scalevalues.
     * If one is not found it will be created.
     */
    public function get_scale_id($scalevalues, $competencyname) {
        global $CFG, $USER;

        require_once($CFG->libdir . '/gradelib.php');

        $allscales = grade_scale::fetch_all_global();
        $matchingscale = false;
        foreach ($allscales as $scale) {
            if ($scale->compact_items() == $scalevalues) {
                $matchingscale = $scale;
            }
        }
        if (!$matchingscale) {
            // Create it.
            $newscale = new grade_scale();
            $newscale->name = get_string('competencyscale', 'tool_lpsync', $competencyname);
            $newscale->courseid = 0;
            $newscale->userid = $USER->id;
            $newscale->scale = $scalevalues;
            $newscale->description = get_string('competencyscaledescription', 'tool_lpsync');
            $newscale->insert();
            return $newscale->id;
        }
        return $matchingscale->id;
    }

    private function set_related($record) {
        $comp = $record->createdcomp;
        if ($record->relatedidnumbers) {
            $allidnumbers = explode(',', $record->relatedidnumbers);
            foreach ($allidnumbers as $rawidnumber) {
                $idnumber = str_replace('%2C', ',', $rawidnumber);

                if (isset($this->flat[$idnumber])) {
                    $relatedcomp = $this->flat[$idnumber]->createdcomp;
                    api::add_related_competency($comp->get_id(), $relatedcomp->get_id());
                }
            }
        }
        foreach ($record->children as $child) {
            $this->set_related($child);
        }
    }

    private function set_rules($record) {
        $comp = $record->createdcomp;
        if ($record->ruletype) {
            $class = $record->ruletype;
            if (class_exists($class)) {
                $oldruleconfig = $record->ruleconfig;
                if ($oldruleconfig == "null") {
                    $oldruleconfig = null;
                }
                $newruleconfig = $class::migrate_config($oldruleconfig, $this->mappings);
                $comp->set_ruleconfig($newruleconfig);
                $comp->set_ruletype($class);
                $comp->set_ruleoutcome($record->ruleoutcome);
                $comp->update();
            }
        }
        foreach ($record->children as $child) {
            $this->set_rules($child);
        }
    }

    /**
     * Do the job.
     */
    public function import() {
        $record = clone $this->framework;
        unset($record->children);

        $record->scaleid = $this->get_scale_id($record->scalevalues, $record->shortname);
        $record->scaleconfiguration = $this->get_scale_configuration($record->scaleid, $record->scaleconfiguration);
        unset($record->scalevalues);
        $record->contextid = context_system::instance()->id;
        
        $framework = api::create_framework($record);

        // Now all the children;
        foreach ($this->framework->children as $comp) {
            $this->create_competency($comp, null, $framework);
        }

        // Now create the rules.
        foreach ($this->framework->children as $record) {
            $this->set_rules($record);
            $this->set_related($record);
        }

        return $framework;
    }
}
