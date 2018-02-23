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

class APIPageView extends JSONResource implements iPageView
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
        $this->headers = apache_get_headers();
        $this->apiVersion = $this->headers['Accept-version'];
        
    }
   
    public function renderPage() {
        echo $this->getResource();
        
    }
}