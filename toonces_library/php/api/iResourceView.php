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
    // Setters and getters
    public function setPageURI();
    public function getPageURI();

    public function setSQLConn();
    public function getSQLConn();

    public function setPageLinkText();
    public function getPageLinkText();
    
    public function setPageTypeID();
    public function getPageTypeID();
   
    // Action methods
    public function __construct();
    public function addElement();
    public function renderPage();
    public function checkSessionAccess();
    public function checkAdminSession();
    
}