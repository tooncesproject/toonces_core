<?php
/*
 * BlogDataResource.php
 * Initial Commit: Paul Anderson, 4/10/2018
 * 
 * A DataResource object definining the inputs and outputs for a Blogs endpoint.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogDataResource extends DataResource implements iResource {
    

    function buildFields() {
        // Define the sub-resources of this resource.
        // url
        $pathName = new StringDataFieldResource(); // "pathName": null,
        $this->fields['pathName'] = $pathName;
        
        $pageURI = new StringDataFieldResource(); // pageURI": "",
        $this->fields['pageURI'] = $pageURI;
        
        $pageTitle = new StringDataFieldResource(); // "pageTitle": "Sorry, This is Toonces.",
        $this->fields['pageTitle'] = $pageTitle;
        
        $pageLinkText = new StringDataFieldResource(); // "pageLinkText": "Home Page",
        $this->fields['pageLinkText'] = $pageLinkText;
        
        $ancestorPageID = new IntegerDataFieldResource(); // "ancestorPageID": null
        $this->fields['ancestorPageID'] = $ancestorPageID;
        
    }
    
    function getAction() {
        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $blogID = $this->validateIdParameter();
        $sqlConn = $this->pageViewReference->getSQLConn();
        
        // Acquire the user id if this is an authenticated request.
        $userID = $this->authenticateUser() ?? 0;
        // Build the query
        $sql = <<<SQL
            SELECT
                 b.blog_id
                ,b.name
                ,b.description
                ,p.page_id
                ,phb.page_id AS ancestor_page_id
                ,bp.blog_post_id
                ,p2.page_id AS blog_post_page_id
                ,bp.title AS blog_post_title
                ,bp.created AS blog_post_created
                ,bp.modified AS blog_post_modified
                ,p2.published
                ,p2.deleted
                ,p2.page_link_text 
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            -- 1st join to PHB is to get the parent page ID
            LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
            -- 2nd join to PHB is to get any children
            LEFT JOIN page_hierarchy_bridge phb2 ON p.page_id = phb2.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            LEFT JOIN blog_posts bp ON b.blog_id = bp.blog_id
            -- 2nd join to pages is for the blog posts
            LEFT JOIN pages p2 ON bp.page_id = p2.page_id
            WHERE
                (b.blod_id = :blogID OR :blogID IS NULL) 
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
            ORDER BY p.page_id ASC
  
SQL;
        // if the id parameter is 0, it's bogus. Only query if it's null or >= 1.
        $result = null;
        if ($blogID !== 0) {
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogID' => $blogID));
            $result = $stmt->fetchAll();
        }

        // Normalize the data.
        // Outer record is the page's metadata,
        // inner record is any children.
        $responseArray = NULL;
        
        if ($result) {
            $lastID = null;
            foreach ($result as $row) {
                // If the outer record has not repeated, create a 'blog' record in the array.
                if ($row['blog_id'] != $lastID) {
                    $blog = array(
                         'pageURI' => GrabPageURL::getURL($row[3], $sqlConn)
                        ,'blogName' => $row[1]
                        ,'blogDescription' => $row[2]
                        ,'pageID' => $row[3]
                        ,'ancestorPageID' => $row[4]
                        ,'blogPosts' => array()
                    );
                }
                $this->dataObjects[$row[0]] = $blog;

                // If the row contains a "child" (AKA blog post), append it to the blog record.
                if (!empty($row[5])) {
                    $blogPost = array(
                         'pageURI' => GrabPageURL::getURL($row[5], $sqlConn)
                        ,'pageID' => $row[6]
                        ,'blogPostTitle' => $row[7]
                        ,'blogPostCreated' => $row[8]
                        ,'blogPostModified' => $row[9]
                        ,'pagePublished' => $row[10]
                        ,'pageDeleted' => $row[11]
                        ,'pageLinkText' => $row[12]
                    );
                    // Append the blog post record to the blog record
                    array_push($this->dataObjects[$row[0]]['blogPosts'], $blog_post);
                }
   
            }
        } else {
            // If no result, status is 404.
            // Note: By a design choice, get requests to unauthorized pages return a 404, not a 401;
            // This is intentional as it obfuscates resources that the user isn't explicity authorized to access.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
        }
        
        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $this->dataObjects;
    }
    
    function postResource($postData) {
        // Validate fields in post data for data type
    }
    
    function putResource($putData) {
        // Validate fields in PUT data for data type
        // Update existing resource
    }
    
    function deleteResource() {
        // Delete resource
    }
    
    
}

