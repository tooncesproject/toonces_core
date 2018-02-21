<?php
/*
 *  CoreAPIResource.php
 *  Initial commit: Paul Anderson, 2/20/2018
 *  
 *   Provides a directory of services available
 *   in the Toonces Core Services REST API.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class CoreAPIResource implements iResource {
    
    public function getResource() {
        $pageID = $this->pageViewReference->pageID;
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
            $resourceArray[$row[2]] = $rootURI . '/' . $row[1];
        
        return $resourceArray;
    }
    
}
