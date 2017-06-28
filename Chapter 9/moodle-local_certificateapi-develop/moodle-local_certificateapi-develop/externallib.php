<?php

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
 * WebServices interface for Certificate API
 *
 * @package    local_certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . "/externallib.php");
require_once(dirname(__FILE__)."/locallib.php");

define('DEBUG_QUERIES', true);
define('DEBUG_TRACE', true);

/**
 * Declaration of the Certificate API class. Contains two fuctions:
 * 
 * 1) get_certificates_by_email()
 * 2) get_certificates_by_username()
 * 
 * See method declarations for more details.
 * 
 * @author Ian Wild
 *
 */
class local_certificateapi_external extends external_api {

    /**
     * Returns description of get_certificates_by_email() method parameters
     * 
     * @return external_function_parameters Array of get_certificates_by_email() method parameters
     */
	public static function get_certificates_by_email_parameters() {
        return new external_function_parameters(
                array('hostid' => new external_value(PARAM_TEXT, 'The ID of the external peer as text.'),
                      'learneremail' => new external_value(PARAM_TEXT, 'The learner email as text.'),
		              'starttime' => new external_value(PARAM_TEXT, 'The start of the time window as text. Optional parameter.', VALUE_DEFAULT, '01/01/1970'),
                      'endtime' => new external_value(PARAM_TEXT, 'The end of the time window as text. Optional parameter.', VALUE_DEFAULT, '01/01/1970')
				)
        );
    }

    /**
     * Returns array of completions from certificate table for given learner (referenced by learner email) within specified time window.
     * 
     * @return array of completions
     * 
     * @param string $hostid Used to identify which public key to use to encrypt the data
     * @param string $learneremail Used to identify a learner in the database.
     * @param string $starttime Start of time window. Defaults to 01/01/1970. Use UK date format.
     * @param string $endtime End of time window. Defaults to 01/01/1970. Use UK date format.
     */
    public static function get_certificates_by_email($hostid = '', $learneremail = '', $starttime='01/01/1970', $endtime='01/01/1970') {
        global $USER, $DB;

        if(DEBUG_TRACE){error_log('get_certificates_by_email(): function called $hostid=' . $hostid . ' $learneremail=' . $learneremail . ' $startime=' . $starttime . ' $endtime='. $endtime);}
        
        if(DEBUG_TRACE){error_log('validating parameters');}
        
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_certificates_by_email_parameters(),
                array('hostid' => $hostid, 'learneremail' => $learneremail, 'starttime' => $starttime, 'endtime' => $endtime));
        
	    if (trim($hostid) == '') {
            throw new invalid_parameter_exception('Invalid peer ID');
        }
        if (trim($learneremail) == '') {
            throw new invalid_parameter_exception('Invalid learner email');
        }
	    if (trim($starttime) == '') {
            throw new invalid_parameter_exception('Invalid start time');
        }
	    if (trim($endtime) == '') {
            throw new invalid_parameter_exception('Invalid end time');
        }
        
        if(DEBUG_TRACE){error_log('context validation');}
        
        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);

        if(DEBUG_TRACE){error_log('checking user capabilities');}
        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
        
       	$starttime = str_replace('/', '-', $starttime);
        $starttime = strtotime($starttime);
        $endtime = str_replace('/', '-', $endtime);
        $endtime = strtotime($endtime);
       
	    $completions = array();
	    
        $sql = 'SELECT
                    user.email AS email,
					course.fullname AS coursename,
                    certissues.timecreated AS issuedate
                FROM {user} AS user
                INNER JOIN {customcert_issues} AS certissues ON user.id = certissues.userid
				INNER JOIN {customcert} AS customcert ON certissues.customcertid = customcert.id 
				INNER JOIN {course} AS course ON customcert.course = course.id
		        WHERE user.email = :learneremail
		        AND certissues.timecreated>=:starttime AND certissues.timecreated<=:endtime';

        $params = array('learneremail'=>$learneremail, 'starttime'=>$starttime, 'endtime'=>$endtime);

    	if(DEBUG_QUERIES){error_log($sql);}
    	if(DEBUG_QUERIES){error_log('learneremail: ' . $params['learneremail'] . ' starttime: ' . $params['starttime'] . ' endtime: ' . $params['endtime'] );}

        $completion_data = $DB->get_records_sql($sql, $params);
        
        if($completion_data) {
            if(DEBUG_TRACE){error_log('completion_data contains ' . count($completion_data) . ' entries.');}
                	
            // add field names so we know what they are the other end
            $fieldnames = new stdClass();
            $fieldnames->coursename = 'coursename';
            $fieldnames->learneremail = 'learnerid';
            $fieldnames->completiondate = 'completiondate';
                
            $completions[] = (array)$fieldnames;
                
            foreach ($completion_data as $data) {
            	$completion = new stdClass();
                              
                $completion->coursename = $data->coursename;
                $completion->learneremail = $data->email;
                $completion->completiondate = $data->issuedate;
                $completions[] = (array)$completion;
            }
        }

         // implode completions into a tab+newline separated string...
        if(DEBUG_TRACE){error_log('Imploding data');}
        $callback = function($value) {
            return implode("\t", $value);
        };
        
        $data = implode("\n", array_map($callback, $completions));
        
        // compress using gzip compression - employ maximum compression
        if(DEBUG_TRACE){error_log('Compressing data');}
        $gzdata = gzencode($data, 9);
        
        // encrypt the data
        if(DEBUG_TRACE){error_log('Encrypting data');}
        $encrypted = certificateapi_encrypt($hostid, $gzdata);
        
        return $encrypted;
    }

    /**
     * Returns description of get_certificates_by_email() method result value
     * 
     * @return external_value Contains compressed and encrypted data.
     */
    public static function get_certificates_by_email_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_TEXT, 'All relevant learner data as an encrypted, compressed array'),
                        'envelope' => new external_value(PARAM_TEXT, 'Data envelope'),
                     )
        );
    }

    /**
     * Returns description of get_employee_completions_by_client_name() function parameters
     * 
     * @return external_function_parameters
     */
    public static function get_certificates_by_username_parameters() {
        return new external_function_parameters(
                array(  'hostid' => new external_value(PARAM_TEXT, 'The ID of the external peer as text.'),
                        'username' => new external_value(PARAM_TEXT, 'The learner username as text'),
                        'starttime' => new external_value(PARAM_TEXT, 'The start of the time window as text', VALUE_DEFAULT, '01/01/1970'),
                        'endtime' => new external_value(PARAM_TEXT, 'The end of the time window as text', VALUE_DEFAULT, '01/01/1970')
                )
        );
    }
    
    /**
     * Returns array of completions from certificate table for learner (referenced by username) 
     * within specified time window.
     * 
     * @param string $hostid Used to identify which public key to use to encrypt the data
     * @param string $username Learner's username.
     * @param string $starttime Start of time window. Defaults to 01/01/1970. Use UK date format.
     * @param string $endtime End of time window. Defaults to 01/01/1970. Use UK date format.
     * 
     * @return array of completions.
     */
    public static function get_certificates_by_username($hostid = '', $username = '', $starttime = '01/01/1970', $endtime = '01/01/1970') {
        global $USER, $DB;
    
        if(DEBUG_TRACE){error_log('get_certificates_by_username(): function called $username='. $username ." $starttime=". $starttime ." $endtime=". $endtime);}
    
        if(DEBUG_TRACE){error_log('validating parameters');}
    
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_certificates_by_username_parameters(),
                array(  'hostid' => $hostid,
                        'username' => $username,
                        'starttime' => $starttime,
                        'endtime' => $endtime));
    
        if (trim($hostid) == '') {
            throw new invalid_parameter_exception('Invalid peer ID');
        }
        if (trim($username) == '') {
            throw new invalid_parameter_exception('Invalid username');
        }
    
        if(DEBUG_TRACE){error_log('context validation');}
    
        //Context validation
        //OPTIONAL but in most web service it should present
        $context = get_context_instance(CONTEXT_USER, $USER->id);
        self::validate_context($context);
    
        if(DEBUG_TRACE){error_log('checking user capabilities');}
        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
    
        // convert start and end times into MySQL format text
        $starttime = str_replace('/', '-', $starttime);
        $starttime = strtotime($starttime);
        $endtime = str_replace('/', '-', $endtime);
        $endtime = strtotime($endtime);
       
        $completions = array();
            
        $sql = 'SELECT
                    user.username AS username,
					course.fullname AS coursename,
                    certissues.timecreated AS issuedate
                FROM {user} AS user
                INNER JOIN {customcert_issues} AS certissues ON user.id = certissues.userid
				INNER JOIN {customcert} AS customcert ON certissues.customcertid = customcert.id 
				INNER JOIN {course} AS course ON customcert.course = course.id
		        WHERE user.username = :username
		        AND certissues.timecreated>=:starttime AND certissues.timecreated<=:endtime';
            
        $params = array('username'=>$username, 'starttime'=>$starttime, 'endtime'=>$endtime);
                
        if(DEBUG_QUERIES){error_log($sql);}
        if(DEBUG_QUERIES){error_log('username: ' . $username . ' starttime: ' . $starttime . ' endtime: ' . $endtime);}
	    
	    $completion_data = $DB->get_records_sql($sql, $params);
    
        if($completion_data) {
            if(DEBUG_TRACE){error_log('completion_data contains ' . count($completion_data) . ' entries.');}

            // add field names so we know what they are the other end
            $fieldnames = new stdClass();
            $fieldnames->coursename = 'coursename';
            $fieldnames->username = 'learnerid';
            $fieldnames->completiondate = 'completiondate';
                
            $completions[] = (array)$fieldnames;
                
		    foreach ($completion_data as $data) {
                $completion = new stdClass();
                $completion->coursename = $data->coursename;
                $completion->username = $data->username;
                $completion->completiondate = $data->issuedate;
		        if(DEBUG_TRACE){error_log('coursename: ' . $completion->coursename . ' username: ' . $completion->username . ' completiondate: ' . $completion->completiondate);}
                $completions[] = (array)$completion;
            }
        }
        
        // implode completions into a tab+newline separated string...
        if(DEBUG_TRACE){error_log('Imploding data');}
        $callback = function($value) {
            return implode("\t", $value);
        };
        
        $data = implode("\n", array_map($callback, $completions));
        
        // compress using gzip compression - employ maximum compression
        if(DEBUG_TRACE){error_log('Compressing data');}
        $gzdata = gzencode($data, 9);
        
        // encrypt the data
        if(DEBUG_TRACE){error_log('Encrypting data');}
        $encrypted = certificateapi_encrypt($hostid, $gzdata);
        
        return $encrypted;
    }
    
    /**
     * Returns description of get_certificates_by_username() method result value
     * 
     * @return external_value Contains compressed and encrypted data.
     */
    public static function get_certificates_by_username_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_TEXT, 'All relevant employee data as an encrypted, compressed array'),
                        'envelope' => new external_value(PARAM_TEXT, 'Data envelope'),
                     )
        );
    }
}
