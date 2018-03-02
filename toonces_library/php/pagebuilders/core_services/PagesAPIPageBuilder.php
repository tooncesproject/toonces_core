<?php
/*
 * 
 * PagesAPIPageBuilder.php
 * Initial commit: Paul Anderson, 2/20/2018
 * 
 * Generates a "Pages" resource for the Toonces Core Services REST API.
 * 
*/
require_once LIBPATH.'php/toonces.php';

class PagesAPIPageBuilder extends APIPageBuilder {
    
    var $apiDelegate;
    var $conn;
    
    function getAction($getParams) {
        
        // Process:
        // If client is authenticated, include unpublished and deleted pages if the user has access.
        // If the GET parameters request a specific Page ID, serve the page metadata.
        $this->conn = $this->pageViewReference->sqlConn;
        $responseArray = array();

        // Instantiate the delegate object
        if (!$this->apiDelegate)
            $this->apiDelegate = new CoreAPIPageBuilderDelegate($this->pageViewReference);
        
        // Check headers validation
        if ($this->apiDelegate->validateHeaders()) {
            $this->performGet($getParams);
        } else {
            header('HTTP/1.1 500 Internal Server Error', true, 510);
        }
    }
    
    function performGet($getParams) {
    
        // Check user authentication
        $userIsAdmin = false;
        $userID = $this->apiDelegate->authenticateUser() ?? 0;
        if ($userID)
            $userIsAdmin = (int)$this->apiDelegate->sessionManager->userIsAdmin;

        // Does the GET request include the parameter 'pageid'?
        $requestedPageID = NULL;
        if (array_key_exists('page_id', $getParams))
                $requestedPageID = $getParams['page_id'];
        
        if ($requestedPageID) {
            // Query the toonces core database for the requested page ID and its associated
            // metadata.
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
                    ,phb.page_id AS ancestor_page_id
                    ,phb2.page_id AS child_page_ids
                FROM toonces.pages p
                JOIN toonces.pagetypes pt ON p.pagetype_id = pt.pagetype_id
                -- 1st join to PHB is to get the parent page ID
                LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
                -- 2nd join to PHB is to get any children
                LEFT JOIN page_hierarchy_bridge phb2 ON p.page_id = phb2.page_id
                LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
                WHERE 
                    p.page_id = :pageID
                    AND
                    (
                      (p.published = 1 AND p.deleted IS NULL)
                      OR
                      pua.user_id IS NOT NULL
                      OR
                      :userIsAdmin = 1
                    )
                ORDER BY p.page_id ASC
SQL;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'pageID' => $pageID, 'userIsAdmin' => $userIsAdmin));
            $result = $stmt->fetchAll();
         
             // Build the array for the DataResource.
             // Outer record is the page's metadata,
             // inner record is any children.
            $topRow = $result[0];
            $responseArray[$pageID] = array(
                  'pageID' => $topRow[0]
                ,'pathName' => $topRow[1]
                ,'pageURI' => GrabPageURL::getURL($topRow[0], $this->conn)
                ,'pageTitle' => $topRow[2]
                ,'pageLinkText' => $topRow[3]
                ,'pagebuilderClass' => $topRow[4]
                ,'pageViewClass' => $topRow[5]
                ,'pageType' => $topRow[8]
                ,'ancestorPageID' =>$topRow[9]
            );
         
            // Make an array of children
            $children = array();
            foreach ($result as $row)
                 array_push($children, $row[10]);
         
            $responseArray['children'] = $children;

        } else {
            // Query the toonces core database for accessible pages.
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
                    ,phb.page_id AS ancestor_page_id
                FROM toonces.pages p
                JOIN toonces.pagetypes pt ON p.pagetype_id = pt.pagetype_id
                LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
                LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
                WHERE (p.published = 1 AND p.deleted IS NULL)
                      OR
                      pua.user_id IS NOT NULL
                      OR
                      :userIsAdmin = 1
                ORDER BY p.page_id ASC
SQL;
    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'userIsAdmin' => $userIsAdmin));
            $result = $stmt->fetchAll();
            
            // Build the array to be returned
            foreach ($result as $row) {
                $pageID = $row[0];
                $responseArray[$pageID] = array(
                    'pathName' => $row[1]
                    ,'pageURI' => GrabPageURL::getURL($pageID, $this->conn)
                    ,'pageTitle' => $row[2]
                    ,'pageLinkText' => $row[3]
                    ,'pagebuilderClass' => $row[4]
                    ,'pageViewClass' => $row[5]
                    ,'pageType' => $row[8]
                    ,'ancestorPageID' => $row[9]
                );
            }
        
        }
        // Create a new DataResource object and populate the builder.
        $dataResource = new DataResource($this->pageViewReference);
        $dataResource->dataObjects = $responseArray;
        array_push($this->resourceArray, $dataResource);

    }

}
