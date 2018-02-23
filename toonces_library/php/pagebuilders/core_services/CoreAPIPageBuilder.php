<?php
/*
 * CoreAPIPageBuilder.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * 
 * Root resource of the Toonces Core Services REST API.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class CoreAPIPageBuilder extends PageBuilder {
    
    function buildPage() {
        // Acquire the necessary data
        $pageID = $this->pageViewReference->pageId;
        $sqlConn = $this->pageViewReference->sqlConn;
        $rootURI = GrabPageURL::getURL($pageID, $sqlConn);
        
        // query the database for any children of this page
        $sql = <<<SQL
            SELECT
                 p.pathname
                ,p.page_title
            FROM
                toonces.page_hierarchy_bridge phb
            JOIN
                toonces.pages p ON phb.descendant_page_id = p.page_id
            WHERE
                phb.page_id = :pageID
SQL;
        
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('pageID' => $pageID));
        $result = $stmt->fetchAll();
        
        $resourceArray = array();
        foreach ($result as $row)
            $resourceArray[$row[1]] = $rootURI . '/' . $row[0];
        
        // Create a new DataResource object and populate the builder.
        $dataResource = new DataResource($this->pageViewReference);
        $dataResource->dataObjects = $resourceArray;
        array_push($this->elementArray, $dataResource);
        
    }
    
}