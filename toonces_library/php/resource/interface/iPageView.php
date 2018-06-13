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
    /**
     * @param PDO $paramSQLConn
     */
    public function setSQLConn($paramSQLConn);

    /**
     * @return PDO
     */
    public function getSQLConn();
    
    // Action methods
    public function __construct($pageViewResourceId);

    /**
     * @param iResource $paramResource
     */
    public function setResource($paramResource);
    public function renderResource();

}