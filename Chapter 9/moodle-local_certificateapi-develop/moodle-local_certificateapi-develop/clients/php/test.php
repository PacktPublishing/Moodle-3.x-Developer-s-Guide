<?php
// This client for local_wstemplate is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC test client for the Certificate API 
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @author Ian Wild
 */

$starttime = '1970-01-01';
$endtime = date('Y-m-d', time());

?>
<HTML><HEAD>
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
    <link rel="stylesheet" href="./styles.css">
    
    <script type="text/javascript">
  
    
    </script>
    
   </HEAD>
<BODY>
<div data-role="page"> 

<div id="page-header" data-role="header" data-role="main" class="header">
    <h1>Certificate API Test</h1>
</div>

<div id="learner-search">
    <form id="search-form" method="get" action="./report.php">
    
    <fieldset data-role="controlgroup">
        <legend>Search on:</legend>
        <input type="radio" name="querytype" id="learner-email-radio" value="learner-email">
        <label for="learner-email-radio">Learner email address</label>
        <input type="radio" name="querytype" id="learner-username-radio" value="learner-username">
        <label for="learner-username-radio">Learner username</label>
    </fieldset>
            
    <fieldset>
        <label for="starttime">Start time:</label>
            <input type="date" name="starttime" id="starttime" value="<?php echo($starttime); ?>">
        <label for="endtime">End time:</label>
            <input type="date" name="endtime" id="endtime" value="<?php echo($endtime); ?>">
    </fieldset>
    
    <input type="search" name="querystring" value="" data-inline="true" data-theme="c" class="search-header" />
    <input type="submit" data-inline="true" value="Search" />
    </form>
</div>

<div data-role="footer" data-position="fixed">
    <div id="task"></div>
</div>

</div>
</BODY></HTML>
  

    
    


