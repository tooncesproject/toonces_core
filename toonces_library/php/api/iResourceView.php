<?php
/*
 * iResourceView.php
 * Initial commit: Paul Anderson, 1/24/2016
 * Project: API/Core/REST refactor
 * Provides an interface for duck-typing "resource view" classes assigned to a "Page"/URI as 
 * delegate to index.php
 */


interface iResourceView
{
    public $pageURI;
    public $pageIsPublished;
    public $sqlConn;
    public $pageLinkText;
    public $pageTypeID;
   
    public function __construct($pageViewPageId);
    public function addElement($element);
    public function renderPage();
    
}