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
 * Web service template plugin related strings
 * @package   local_certificateapi
 * @copyright 2017 Ian Wild
 * @author    Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Certificate API';
$string['enabled'] = 'Enable Certificate API';
$string['api_enabled_desc'] = 'The Certificate API uses an encrypted XMLRPC connection. <a href="{$a}">Click here</a> to configure connections.';

$string['addnewhost'] = 'Add new connection';
$string['fullname'] = 'Full name';
$string['fullname_help'] = 'This is the human readable name of the external application that will be wanting to connect with us.';
$string['hostname'] = 'Host name';
$string['hostname_help'] = 'The host name is typically a domain name - e.g. mycharity.org.uk. This is checked against the common name (CN) and subject alternative name (SubjectAltName) in the client\'s public key.';
$string['clientid'] = 'Organisational identifier';
$string['clientid_help'] = 'The client id is typically a unique identifier that is checked against the organisation (O) in the client\'s public key';
$string['addhost'] = 'Add host';
$string['hostexists'] = 'Host {$a} already configured';
$string['last_connect_time'] = 'Last connect time';

$string['edithost'] = 'Edit connection';
$string['peer_config_desc'] = 'For reasons of security, the client should provide you with a public key. <a href="{$a}">Click here</a> to create a new x509 public/private key pair.';
$string['wwwroot'] = 'External client web address';
$string['publickey'] = 'Client public key';
$string['publickey_help'] = 'This is an x509 public key. Note that the common and subject alternative names need to match the host name (above) and the organisation name needs to match the organisational identifier (above).';
$string['expires'] = 'Connection expires';
$string['expired'] = 'Expired';
$string['certdetails'] = 'Certificate details';
$string['ipaddress'] = 'IP Address';
$string['currentkey'] = 'Current key';
$string['never'] = 'Never';
$string['invalidpubkey'] = 'This public key is not valid';
$string['createkey'] = 'Create key pair';
$string['startdate'] = 'Start date';
$string['enddate'] = 'End date';
$string['connectionkeys'] = 'New keys';

$string['countryName'] = '2 character country code';
$string['stateOrProvinceName'] = 'State or province';
$string['localityName'] = 'Locality';
$string['organizationName'] = 'Organisation';
$string['organizationalUnitName'] = 'Organisational unit';
$string['commonName'] = 'Common name';
$string['subjectAltName'] = 'Subject alternative name';
$string['emailAddress'] = 'Email address';
$string['keytimewindow'] = 'Please note that the public key provided is used for encryption only: the time window within which access is permitted is configured in the platform, rather than specified in the key.';

$string['publickey_desc'] = 'You may also wish to give the following public key to the client, at least for their records. Again, secrecy is paramount. We will use this key to encrypt data passed to the client:';
$string['privatekey_desc'] = 'The following x509 private key will need to be provided to the client. Note that this key MUST be kept secret from all (except the client, obviously!):';

$string['deletehost'] = 'Delete an external host';
$string['deletehostcheck'] = 'Are you absolutely sure you want to completely delete this external host and all the data it contains?';
$string['deletinghost'] = 'Deleting {$a}';
$string['deletedhost'] = '{$a} has been completely deleted';
$string['invalidhostid'] ='{$s} is not a valid external host';

$string['hostupdate'] = '{$a} has been updated';

$string['keynotyetvalid'] = 'You are not yet allowed to connect';
$string['keynolongervalid'] = 'You are no longer allowed to connect';
$string['certfault'] = 'Certificate is faulty';
$string['invalidhost'] = 'Remote peer has an invalid ID';

$string['certificateapinotenabled'] = 'Certificate API is not enabled';
