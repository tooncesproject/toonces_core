<?php
/*
 * iAPIPageBuilderDelegate.php
 * Initial commit: Paul Anderson, 2/25/2018
 * 
 * Interface defining common functionality for delegates of REST API PageBuilder classes.
 * 
*/

require_once LIBPATH.'php/toonces.php';

interface iAPIPageBuilderDelegate {
    
    function authenticateUser();
    function detectAPIVersion();
    function validateHeaders();

}