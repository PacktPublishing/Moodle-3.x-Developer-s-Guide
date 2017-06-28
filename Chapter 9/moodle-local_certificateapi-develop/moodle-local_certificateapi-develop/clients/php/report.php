<?php
// This client for local_wstemplate is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_certificateapi
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @author Ian Wild
 */

/// PARAMETERS
$querytype = 0;
$querystring = "";
$starttime = '01/01/1970';
$endtime = date('Y-m-d', time());

define("LEARNER_EMAIL", 0);
define("LEARNER_USERNAME", 1);

if(isset($_GET['querytype'])) {
    if($_GET['querytype'] === "learner-email") {
    	$querytype = LEARNER_EMAIL;
    }
    if($_GET['querytype'] === "learner-username") {
    	$querytype = LEARNER_USERNAME;
    }
}
if(isset($_GET['starttime'])) {
    $starttime = $_GET['starttime'];
}
if(isset($_GET['endtime'])) {
    $endtime = $_GET['endtime'];    
}
if(isset($_GET['querystring'])) {
    $querystring = $_GET['querystring'];
}

$hostid = "testing";

include_once dirname(__FILE__).'/locallib.php';
?>

<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no">

<script src="http://code.jquery.com/jquery-1.12.0.min.js"></script>
<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
<script src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="http://cdn.rawgit.com/jquery/jquery-ui/1.10.4/ui/jquery.ui.datepicker.js"></script>
<script id="mobile-datepicker" src="http://cdn.rawgit.com/arschmitz/jquery-mobile-datepicker-wrapper/v0.1.1/jquery.mobile.datepicker.js"></script>

<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
<link rel="stylesheet" type="text/css" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile.structure-1.4.5.css">
<link rel="stylesheet" href="http://cdn.rawgit.com/arschmitz/jquery-mobile-datepicker-wrapper/v0.1.1/jquery.mobile.datepicker.css">
<link rel="stylesheet" href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css">


   <script type="text/javascript">
      
   jQuery.extend( jQuery.fn.dataTableExt.oSort, {
		"num-html-pre": function ( a ) {
			var numb = a.match(/\d/g);
			numb = numb.join("");
			return parseFloat( numb );
		},

		"num-html-asc": function ( a, b ) {
			return ((a < b) ? -1 : ((a > b) ? 1 : 0));
		},

		"num-html-desc": function ( a, b ) {
			return ((a < b) ? 1 : ((a > b) ? -1 : 0));
		},

		"date-uk-pre": function ( a ) {
	        if (a == null || a == "") {
	            return 0;
	        }
	        var htmlObject = $(a);
	        var dtstr = htmlObject.text();
	        // convert date to number
	        var timestamp = new Date(dtstr.split("/").reverse().join("-")).getTime();
	        return timestamp;
	    },
	 
	    "date-uk-asc": function ( a, b ) {
	        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	    },
	 
	    "date-uk-desc": function ( a, b ) {
	        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
	    }
	} );

   
   $(document).ready(function() {
	       $('#summary-table').DataTable( {
		       "order": [[ 1, "desc" ]],
		       "columns": [
		         			{ "sType": "num-html" },
		         			null,
		         			{ 
		         	            "sType": 'date-uk',
		         	            "render": function ( data, type, row ) {
		         	            	var n = data.lastIndexOf('>');
		         	            	var result = data.substring(n + 1);
		         	            	var date = new Date(result * 1000);
		         	                javascriptDate = date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear();
		         	                return "<div class='date'>"+javascriptDate+"<div>";		         	            }
		         	        }
		         		]
		   });

	       $('#cancel_btn').on('click', function(e) {
	          	e.preventDefault();
	              e.stopPropagation();
	              e.stopImmediatePropagation();
	              
	          	var client_id = this.dataset.clientid;
	          	var href = $(this).attr('href')+'?clientid='+client_id;
	          	
	          	window.location.href = href;
	      });    
		});
   	

	
    </script>
</HEAD>
<BODY>
<div data-role="page">
<div id="page-header" data-role="header" data-role="main" class="header">
    <h1>Learner data</h1>
    
</div>
<?php
    if(!isset($learnerdata)) {
        // get client summary from platform
        switch ($querytype) {
        	case LEARNER_EMAIL:
                $learnerdata = call_api('local_certificateapi_get_certificates_by_email', array($hostid, $querystring, $starttime, $endtime));
                break;
        	case LEARNER_USERNAME:
                $learnerdata = call_api('local_certificateapi_get_certificates_by_username', array($hostid, $querystring, $starttime, $endtime));
                break;
            default:
                break;
        }
    } 
?>
	
<div data-role="content">
	
        <table data-role="table" id="summary-table" data-filter="true" data-input="#filterTable-input" class="display ui-responsive ui-bar-d table-stripe">
            <thead>
                <tr>
                    <th data-priority="persist">Learner</th>
                    <th data-priority="persist">Course</th>
                    <th data-priority="persist">Completed</th>
                </tr>
            </thead>
                
            <tbody>
<?php 
    foreach($learnerdata as $data) {
        echo('<tr>');
        echo('<td>' . $data['learnerid'] . '</td>');
        echo('<td>' . $data['coursename'] . '</td>');
        echo('<td>' . $data['completiondate'] . '</td>');
        echo('</tr>');
    }

?>        
            </tbody>
        </table>
    </div>

<div data-role="footer" data-position="fixed"> 
    <a id="cancel_btn" href="./test.php" data-clientid="' . $child['clientid'] . '" data-theme="c" data-mini="true" data-inline="true" class="info_btn" data-role="button" data-icon="home">Home</a>
</div>
</div>
</body></html>
 

