<?php
/*
 * JsonPageView.php
 * Initial commit: Paul Anderson, 1/24/2018
 * 
 * iView implementation for REST API resources.
 * Analagous to the web view renderer element HTMLPageView.
 * Generates a resource's JSON response.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class JsonPageView implements iPageView, iResource
{
 
    // iPageView interface vars
    public $pageURI;
    public $sqlConn;
    public $pageLinkText;
    public $pageTypeID;
    
    public $sessionManager;
    
    var $queryArray = array();
    var $headers = array();
    var $dataObjects = array();
    var $HTTPMethod;
    var $pageId;
    
    public $apiVersion;
    
    
    // iPageView setters and getters
    public function setPageURI($paramPageURI) {
        $this->pageURI = $paramPageURI;
    }
    
    public function addElement($element) {
        array_push($this->dataObjects, $element);
    }

    public function getPageURI() {
        if (!$this->pageURI)
            $this->setPageURI(GrabPageURL::getURL($this->pageId, $this->sqlConn));
 
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
        // Detect API version
        $this->apiVersion = $_SERVER['HTTP_ACCEPT_VERSION'];
    }

    public function getResource() {
        // Overrides DataResource->getResource()
        // Validates the nested resource and returns it.
        
        // JsonPageView allows only a single root resource object; throw an exception if
        // this isn't the case.
        if (count($this->dataObjects) != 1)
            throw new Exception('Error: An APIPageBuilder must instantiate one and only one data object per page.');

        return $this->dataObjects[0];
        
    }
   
    public function renderPage() {
        // Called by index.php - Converts the resource data to JSON, executes the server's response. 
        $dataObject = $this->getResource();

        // Execute the object
        $resourceData = $dataObject->getResource();
        // If the resource has a status message, add it to the output
        if ($dataObject->statusMessage)
            $resourceData['status'] = $dataObject->statusMessage;

        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $dataObject->httpStatus;
        $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');
        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');
        
        // Encode as JSON and render.
        $JSONString = json_encode($resourceData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        header($httpStatusString, true, $httpStatus);
        echo($JSONString);
        
    }
}