<?php

function decrypt($response) {
    $output = false;
    
    if(count($response) == 2) {
        // response should have 'data' and an 'envelope'
        
        if(isset($response['data']) && isset($response['envelope'])) {
            $data = base64_decode($response['data']);
            $envelope = base64_decode($response['envelope']);
            
            // load private key from file
            static $certificateapi_key= null;
            
            if ($certificateapi_key === null) {
            	$certificateapi_key= file_get_contents(dirname(__FILE__).'/key.txt', false);
            }
                            
            // Initialize payload var
            $decryptedenvelope = '';
            $isOpen = openssl_open($data, $decryptedenvelope, $envelope, $certificateapi_key);
            
            if ($isOpen) {
                $output = $decryptedenvelope;
            }
        }
    }
    
    return $output;
}

function decompress($string) {
    $data = gzdecode($string);

    $result = array();

    foreach (explode("\n", $data) as $row) {
        $result[] = explode("\t", $row);
    }

    return $result;
}

/**
 * Create an associative data array from a multidimentional array. The first row represents the names for association
 */
function associate_data($data) {
    $associations = array_shift($data);

    $associativeArray = array();

    foreach ($data as $row) {
        $dataBlock = array();
        $index = 0;
        foreach ($row as $element) {
            $dataBlock[$associations[$index++]] = $element;
        }

        $associativeArray[] = $dataBlock;
    }

    return $associativeArray;
}

function call_api ($functionname, $params=array()) {
    static $token = null;
    if ($token === null) {
        $token = file_get_contents(dirname(__FILE__).'/token.txt', false);
    }
      
    // LOCAL
    $domainname = 'http://moodle316.localhost';
    
    $serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
    require_once('./curl.php');
    $curl = new curl;
    $post = xmlrpc_encode_request($functionname, $params);
    $response = $curl->post($serverurl, $post);
    $resp = xmlrpc_decode($response);

    // decrypt raw data to a compressed string
    $compressed = decrypt($resp);
    // decompress raw data into binary block
    $data = decompress($compressed);
    $learnerdata = associate_data($data);
    
    return $learnerdata;
}

