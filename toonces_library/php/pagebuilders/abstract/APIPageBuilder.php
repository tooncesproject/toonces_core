<?php
/*
 * APIPageBuilder.php
 * Initial commit: Paul Anderson, 1/24/2018
 * Base abstract class extending PageBuilder to form the root resource of a REST API "page."
 * 
 */

require_once LIBPATH.'php/toonces.php';

abstract class APIPageBuilder
{

    var $elementArray;
    var $apiDelegate;
    var $implementedMethods = array();
    var $pageViewReference;
    
    function __construct($pageview) {
        $this->pageViewReference = $pageview;
    }
    
    // For each HTTP method, the default response is a general error.
    function getAction($getParams) {
        // Override this method to generate the API's response to GET requests.
        // $getParams provides an array of URL parameters.
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        $responseArray = array();
        return $responseArray;
    }
    
    function postAction($postData) {
        // Override this method to generate the API's response to POST requests.
        // the buildPage() method provides the $postData parameter.
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        $responseArray = array();
        return $responseArray;
    }

    function headAction($getParams) {
        // Override this method to generate the API's response to HEAD requests.
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        $responseArray = array();
        return $responseArray;
    }
    
    function putAction($putData) {
        // Override this method to generate the API's response to PUT requests.
        // the buildPage() method provides the $putData parameter.
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        $responseArray = array();
        return $responseArray;
    }
    
    function optionsAction() {
        // Override this method to generate the API's response to OPTIONS requests.
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        $responseArray = array();
        return $responseArray;
    }
    
    function deleteAction($deletePayload) {
        // Override this method to generate the API's response to DELETE requests.
        // the buildPage() method provides the $deletePayload parameter.
        $responseArray = array();
        return $responseArray;
    }
    
    function connectAction() {
        // Override this method to generate the API's response to CONNECT requests.
        $responseArray = array();
        return $responseArray;
    }
    
    function buildPage() {
        // Unlike the HTML PageBuilder, you shouldn't override this method. Instead,
        // override the "action" methods to determine the resource's behavior based
        // on the HTTP method of the client's request.
        $pageArray = array();
        
        // Detect HTTP method and call the appropriate method.
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $pageArray = $this->getAction($_GET);
                break;
            case 'POST':
                $pageArray = $this->postAction($_POST);
                break;
            case 'HEAD':
                $pageArray = $this->headAction($_GET);
                break;
            case 'PUT':
                $pageArray = $this->putAction(file_get_contents('php://input'));
                break;
            case 'OPTIONS':
                break;
            case 'DELETE':
                $pageArray = $this->deleteAction(file_get_contents('php://input'));
            case 'CONNECT':
                $pageArray = $this->connectAction();
                break;
        }
        
        // If the data returned is not an array, the implementation is not valid.
        if (!is_array($pageArray)) 
            throw new Exception('Implementation error: The value returned by any HTTP Action methods overridden from APIPageBuilder must be an array.');
        
        // Return the response so the PageBuilder can render the resource.
        return $pageArray;
    }
}