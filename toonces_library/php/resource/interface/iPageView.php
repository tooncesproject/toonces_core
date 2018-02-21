<?php
/*
 * iPageView.php
 * Initial commit: Paul Anderson, 1/24/2016
 * Project: API/Core/REST refactor
 * Provides an interface for duck-typing "resource view" classes assigned to a "Page"/URI as 
 * delegate to index.php
 */


interface iPageView
{
    // Setters and getters
    public function setPageURI($paramPageURI);
    public function getPageURI();

    public function setSQLConn($paramSQLConn);
    public function getSQLConn();

    public function setPageLinkText($paramPageLinkText);
    public function getPageLinkText();
    
    public function setPageTypeID($paramPageTypeID);
    public function getPageTypeID();
   
    // Action methods
    public function __construct($pageViewPageId);
    public function addElement($element);
    public function renderPage();
    public function checkSessionAccess();
    public function checkAdminSession();
    
}