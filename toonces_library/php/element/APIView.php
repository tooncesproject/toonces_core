<?php
/*
 * APIView.php
 * Initial commit: Paul Anderson, 1/24/2018
 * 
 * iView implementation for REST API resources.
 * Analagous to the web view renderer element PageView.
 * Generates a resource's JSON response.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class APIView extends JSONElement implements iResourceView
{
 
    // iResourceView interface vars
    public $pageURI;
    public $pageIsPublished;
    public $sqlConn;
    public $pageLinkText;
    public $pageTypeID;
    
    var $apiVersion;
    var $queryArray = array();
    var $pageId;
    
    public function __construct($pageViewPageId) {
        $this->pageId = $pageViewPageId;
        parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
    }
   
    public function renderPage() {
        
        echo $this->getResource();
        
    }
}