<?php
/*
 * APIPageView.php
 * Initial commit: Paul Anderson, 1/24/2018
 * 
 * iView implementation for REST API resources.
 * Analagous to the web view renderer element HTMLPageVew.
 * Generates a resource's JSON response.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class APIPageView implements iPageView, iResource
{
 
    // iPageView interface vars
    public $pageURI;
    public $sqlConn;
    public $pageLinkText;
    public $pageTypeID;
    
    var $apiVersion;
    var $queryArray = array();
    var $headers = array();
    var $HTTPMethod;
    var $pageId;
    var $dataObjects = array();
    
    public function __construct($pageViewPageId) {
        $this->pageId = $pageViewPageId;
        parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
        $this->HTTPMethod = $_SERVER['REQUEST_METHOD'];
        // $this->headers = apache_get_headers();
        // $this->apiVersion = $this->headers['Accept-version'];
    }

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

    public function addElement ($element) {
        array_push($this->dataObjects,$element);
    }
 
    public function getResource() {
        // Overrides DataResource->getResource()
        // Iterate through the dataObjects array, creating a new array with its extracted contents
        $pageArray = array();
        foreach ($this->dataObjects as $dataResource)
            array_push($pageArray, $dataResource->getResource());

         // Encode as JSON and return.
         $JSONString = json_encode($pageArray, JSON_PRETTY_PRINT);
            return $JSONString;
    }
   
    public function renderPage() {
        echo $this->getResource();
        
    }
}