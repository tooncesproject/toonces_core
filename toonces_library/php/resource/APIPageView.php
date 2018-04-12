<?php
/*
 * APIPageView.php
 * Initial commit: Paul Anderson, 1/24/2018
 * 
 * iView implementation for REST API resources.
 * Analagous to the web view renderer element HTMLPageView.
 * Generates a resource's JSON response.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class APIPageView extends DataResource implements iPageView, iResource
{
 
    // iPageView interface vars
    public $pageURI;
    public $sqlConn;
    public $pageLinkText;
    public $pageTypeID;
    
    public $sessionManager;
    var $apiVersion;
    var $queryArray = array();
    var $headers = array();
    var $HTTPMethod;
    var $pageId;
    // inherited from DataResource:
    //var $dataObjects = array();
    
    // iPageView setters and getters
    public function setPageURI($paramPageURI) {
        $this->pageURI = $paramPageURI;
    }
    
    public function getPageURI() {
        return $this->pageURI;
    }
    
    public function setSQLConn($paramSQLConn) {
        $this->sqlConn = $paramSQLConn;
    }

    public function getSQLConn() {
        return $this->sqlConn;
    }

    public function setPageLinkText($paramPageLinkText) {
        $this->pageLinkText = $paramPageLinkText;
    }

    public function getPageLinkText() {
        return $this->pageLinkText;
    }
    
    public function setPageTypeID($paramPageTypeID) {
        $this->pageTypeID = $paramPageTypeID;
    }
    
    public function getPageTypeID() {
        return $this->pageTypeID;
    }

    public function setPageTitle($paramPageTitle) {
        // Do nothing 
    }
    
    public function getPageTitle() {
        // API pages do not have page titles.
        return NULL;
    }
    
    public function checkSessionAccess() {
        // REST APIs are stateless; therefore session access does not apply.
        // Defaults to True; any authentication is handled by the resource itself.
        return true;
    }
    
    public function checkAdminSession() {
        // Similarly, admin session does not apply.
        return false;
    }
    
    public function __construct($pageViewPageId) {
        $this->pageId = $pageViewPageId;
        parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
        $this->HTTPMethod = $_SERVER['REQUEST_METHOD'];   
    }

    public function getResource() {
        // Overrides DataResource->getResource()
        // Iterate through the dataObjects array, creating a new array with its extracted contents
        
        
        
        /*
        $pageArray = array();
        foreach ($this->dataObjects as $dataResource)
            array_push($pageArray, $dataResource->getResource());
        
         // Encode as JSON and return.
         $JSONString = json_encode($pageArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            return $JSONString;
        */
    }
   
    public function renderPage() {
        // APIPageView allows only a single root resource object; throw an exception if 
        // this isn't the case.
        // TODO: FIX THIS
        /*
        if (count($this->dataObjects != 1))
            throw new Exception('Error: An APIPageBuilder must instantiate one and only one data object per page.');
        */  
        $dataObject = $this->dataObjects[0];
        
        $dataOut = array();
        // Execute the object
        $resourceData = $dataObject->getResource();
        // If the resource has a status message, add it to the output
        if ($dataObject->statusMessage)
            $resourceData['status'] = $dataObject->statusMessage;
        
        // Append the resource's output
        array_push($dataOut, $resourceData);
        
        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $dataObject->httpStatus;
        $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');
        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');
        
        // Encode as JSON and render.
        $JSONString = json_encode($dataOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        header($httpStatusString, true, $httpStatus);
        echo($JSONString);
        
    }
}