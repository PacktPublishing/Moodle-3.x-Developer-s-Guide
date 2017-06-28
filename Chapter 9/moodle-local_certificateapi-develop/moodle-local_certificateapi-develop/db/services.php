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

// We defined the web service functions to install.
$functions = array(
        'local_certificateapi_get_certificates_by_email' => array(
                'classname'   => 'local_certificateapi_external',
                'methodname'  => 'get_certificates_by_email',
                'classpath'   => 'local/certificateapi/externallib.php',
                'description' => 'Return array of learner completion dates. Requires learner email as a parameter',
                'type'        => 'read',
        ),
        'local_certificateapi_get_certificates_by_username' => array(
                'classname'   => 'local_certificateapi_external',
                'methodname'  => 'get_certificates_by_username',
                'classpath'   => 'local/certificateapi/externallib.php',
                'description' => 'Return array of learner completion dates. Requires learner username as a parameter',
                'type'        => 'read',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Certificate API service' => array(
                'functions' => array (  'local_certificateapi_get_certificates_by_email',
                                        'local_certificateapi_get_certificates_by_username'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
