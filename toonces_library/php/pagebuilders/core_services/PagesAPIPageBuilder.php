<?php
/*
 * 
 * PagesAPIPageBuilder.php
 * Initial commit: Paul Anderson, 2/20/2018
 * 
 * Generates a "Pages" resource for the Toonces Core Services REST API.
 * 
*/

class PagesAPIPageBuilder extends PageBuilder {
    
    function buildPage() {
        $conn = $this->pageViewReference->sqlConn;
        
        // Query the toonces core database for all published pages.
        $sql = <<<SQL
            SELECT
                 p.page_id
                ,p.pathname
                ,p.page_title
                ,p.page_link_text
                ,p.pagebuilder_class
                ,p.pageview_class
                ,p.created_dt
                ,p.modified_dt
                ,pt.name AS page_type
                ,phb.ancestor_page_id
            FROM toonces.pages p
            JOIN toonces.pagetypes pt ON p.pagetype_id = pt.pagetype_id
            LEFT JOIN (
                SELECT page_id, MIN(ancestor_page_id) AS ancestor_page_id
                FROM toonces.page_hierarchy_bridge
                GROUP BY page_id
            ) phb ON p.page_id = phb.page_id
            WHERE p.published = 1 AND p.deleted = 0
            ORDER BY p.page_id ASC
SQL;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        // Build the array to be returned
        $resourceArray = array();
        
        foreach ($result as $row) {
            $pageID = $row[0];
            $resourceArray[$pageID] - array(
                'pathName' => $row[1]
                ,'pageURI' => GrabPageURL::getURL($pageID, $conn)
                ,'pageTitle' => $row[2]
                ,'pageLinkText' => $row[3]
                ,'pagebuilderClass' => $row[4]
                ,'pageViewClass' => $row[5]
                ,'pageType' => $row[8]
                ,'ancestorPageID' => $row[9]
            );
        }
        
        // Create a new DataResource object and populate the builder.
        $dataResource = new DataResource($this->pageViewReference);
        $dataResource->dataObjects = $resourceArray;
        array_push($this->elementArray, $dataResource);
        
    }
}