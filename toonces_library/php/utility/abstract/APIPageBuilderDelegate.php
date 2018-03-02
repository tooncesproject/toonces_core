<?php
/*
 * APIPageBuilderDelegate.php
 * Initial commit: Paul Anderson, 2/25/2018
 * 
 * An abstract class providing basic functionality for API page builder delegate classes.
 * 
*/

require_once LIBPATH.'php/toonces.php';

abstract class APIPageBuilderDelegate implements iAPIPageBuilderDelegate {
    
    function authenticateUser()
    {
        // APIPageBuilderDelegate subclasses must override this method if they
        // require authentication.
        return false;
    }

    function detectAPIVersion() {
        // You'll only ever need to override this method if you use a different system of
        // versioning from the default (Accept-version header).
        $headers = apache_request_headers();
        return $headers['Accept-version'];
        
    }
    
    function validateHeaders() {
        // Override if the resource requires any specific HTTP headers - Validate them here.
        // Return true/false: headers are valid.
        return true;
    }
    
}