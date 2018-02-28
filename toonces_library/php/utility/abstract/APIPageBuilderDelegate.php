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
        // Most of the time, you shouldn't need to override this method.
    }
    
    
}