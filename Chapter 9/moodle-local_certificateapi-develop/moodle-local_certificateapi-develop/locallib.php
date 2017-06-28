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

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/peer.php');

/**
 * Return information about all the current hosts
 * This is basically just a resultset.
 *
 * @return array
 */
function certificateapi_get_host($clientid) {
    $peer = new certificateapi_peer();

    $result = $peer->load_peer_from_clientid($clientid);
    
    if($result) {
        return $peer;
    }
    
    return false;
}

/**
 * A function to encrypt a string.
 * @author Ian Wild
 *
 * Encrypt using OpenSSL (AES-256-CBC method). Encryption key is specified in the platform's main
 * configuration file. Initialisation vector is
 *
 * @param string $string The string to be encrypted.
 *
 * @return string/boolean The encrypted string or false on error.
 */
function certificateapi_encrypt($clientid, $message) {
    global $CFG;

    $output = false;

    $host = certificateapi_get_host($clientid);
    
    if($host != false) {
        // Is time window for host valid?
        if(time() < $host->public_key_validfrom) {
           $output = array();
           $output['data'] = get_string('keynotyetvalid', 'local_certificateapi');
           $output['envelope'] = ''; 
        } elseif (time() > $host->public_key_expires) {
           $output['data'] = get_string('keynolongervalid', 'local_certificateapi');
           $output['envelope'] = '';
        } elseif (get_config('local_certificateapi', 'enabled') == false) {
           $output['data'] = get_string('certificateapinotenabled', 'local_certificateapi');
           $output['envelope'] = '';
        } else {
            // Generate a key resource from the remote_certificate text string
            $publickey = openssl_get_publickey($host->public_key);
            
            if ( gettype($publickey) != 'resource' ) {
                // Remote certificate is faulty.
                $output = get_string('certfault', 'local_certificateapi');
            } else {
                // Initialize vars
                $encryptedstring = '';
                $symmetric_keys = array();
                
                //        passed by ref ->     &$encryptedstring &$symmetric_keys
                $bool = openssl_seal($message, $encryptedstring, $symmetric_keys, array($publickey));
                $message = $encryptedstring;
                $symmetrickey = array_pop($symmetric_keys);
                
                $output = array();
                $output['data'] = base64_encode($message);
                $output['envelope'] = base64_encode($symmetrickey);
                }
            }
            
            // update the last connect time, regardless of permissions or time window.
           $host->touch();
        } else {
            $output = get_string('invalidhost', 'local_certificateapi');
        }

    return $output;
}

/**
 * Return information about all the current hosts
 * This is basically just a resultset.
 *
 * @return array
 */
function certificateapi_get_hosts() {
    global $CFG, $DB;
    return $DB->get_records_sql('SELECT
                                    h.id,
                                    h.ip_address,
                                    h.wwwroot,
                                    h.fullname,
                                    h.last_connect_time,
                                    h.clientid,
                                    h.public_key,
                                    h.public_key_validfrom,
                                    h.public_key_expires,
                                    h.permissions
                                 FROM
                                    {certificateapi_host} AS h' );
}

/**
 * Generate public/private keys
 *
 * Use the distinguished name provided to create a CSR, and then sign that CSR
 * with the same credentials. Return the keypair you create.
 *
 * @param   array  $dn  The distinguished name of the server
 * @return  string      The signature over that text
 */
function certificateapi_generate_keypair($dn = null, $days=28) {
    global $CFG, $USER, $DB;

    $keypair = array();

    if (is_null($dn)) {
        return false;
    }

    $dnlimits = array(
            'countryName'            => 2,
            'stateOrProvinceName'    => 128,
            'localityName'           => 128,
            'organizationName'       => 64,
            'organizationalUnitName' => 64,
            'commonName'             => 64,
            'emailAddress'           => 128
    );

    foreach ($dnlimits as $key => $length) {
        $dn[$key] = substr($dn[$key], 0, $length);
    }

    // ensure we remove trailing slashes
    $dn["commonName"] = preg_replace(':/$:', '', $dn["commonName"]);
    if (!empty($CFG->opensslcnf)) { //allow specification of openssl.cnf especially for Windows installs
        $new_key = openssl_pkey_new(array("config" => $CFG->opensslcnf));
    } else {
        $new_key = openssl_pkey_new();
    }
    if ($new_key === false) {
        // can not generate keys - missing openssl.cnf??
    	$error = openssl_error_string();
        return null;
    }
    if (!empty($CFG->opensslcnf)) { //allow specification of openssl.cnf especially for Windows installs
        $csr_rsc = openssl_csr_new($dn, $new_key, array("config" => $CFG->opensslcnf));
        $selfSignedCert = openssl_csr_sign($csr_rsc, null, $new_key, $days, array("config" => $CFG->opensslcnf));
    } else {
        $csr_rsc = openssl_csr_new($dn, $new_key, array('private_key_bits',2048));
        $selfSignedCert = openssl_csr_sign($csr_rsc, null, $new_key, $days);
    }
    unset($csr_rsc); // Free up the resource

    // We export our self-signed certificate to a string.
    openssl_x509_export($selfSignedCert, $keypair['certificate']);
    openssl_x509_free($selfSignedCert);

    // Export your public/private key pair as a PEM encoded string. You
    // can protect it with an optional passphrase if you wish.
    if (!empty($CFG->opensslcnf)) { //allow specification of openssl.cnf especially for Windows installs
        $export = openssl_pkey_export($new_key, $keypair['keypair_PEM'], null, array("config" => $CFG->opensslcnf));
    } else {
        $export = openssl_pkey_export($new_key, $keypair['keypair_PEM'] /* , $passphrase */);
    }
    openssl_pkey_free($new_key);
    unset($new_key); // Free up the resource

    return $keypair;
}
