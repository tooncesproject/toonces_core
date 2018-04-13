<?php
/*
 * BlogPostDataResource.php
 * Initial commit: Paul Anderson, 4/12/2018
 * 
 * A DataResource class definining the inputs and outputs for a Blog Posts endpoint.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogPostDataResource extends DataResource implements iResource {
    
    function getAction() {
        // Implementation lives here!
        // We will query the database for the resource, depending upon parameters
        $sqlConn = $this->pageViewReference->getSQLConn();
       
        // First - Validate GET parameters
        $blogPostID = $this->validateIntParameter('id');
        $blogID = $this->validateIntParameter('blog_id');
        
        // Acquire the user id if this is an authenticated request.
        $userID = $this->authenticateUser() ?? 0;
        
        // Build the query
        $sql = <<<SQL
            SELECT
                 bp.blog_post_id
                ,bp.blog_id
                ,bp.page_id
                ,bp.created_dt
                ,bp.modified_dt
                ,bp.deleted
                ,u.user_id
                ,bp.title
                ,bp.body
                ,bp.thumbnail_image_vector
            FROM blog_posts bp
            JOIN pages p ON p.page_id = bp.page_id
            LEFT JOIN page_user_access pua ON bp.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                (bp.blog_id = :blogID OR :blogID IS NULL)
                AND
                (bp.blog_post_id = :blogPostID OR :blogPostID IS NULL) 
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
            ORDER BY bp.blog_id, bp.blog_post_id ASC
SQL;
        // if the id parameters are 0, they're bogus. Only query if it's null or >= 1.
        $result = null;
        if ($blogID !== 0 && $blogPostID !== 0) {
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogID' => $blogID, 'blogPostID' => $blogPostID));
            $result = $stmt->fetchAll();
        }

        // Serialize the output as an array.
        $responseArray = NULL;
        
        if (count($result) > 0) {
            // 
            foreach ($result as $row) {
                $blogPost = array(
                     'url' => $this->resourceURL . '?id=' . strval($row[0])
                    ,'blog_id' => $row[1]
                    ,'page_id' => $row[2]
                    ,'page_uri' => GrabPageURL::getURL($row[2], $sqlConn)
                    ,'created_dt' => $row[3]
                    ,'modified_dt' => $row[4]
                    ,'deleted' => $row[5]
                    ,'user_id' => $row[6]
                    ,'title' => $row[7]
                    ,'body' => $row[8]
                    ,'thumbnail_image_vector' => $row[9]
                );
                $this->dataObjects[$row[0]] = $blogPost;
            }
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            // If no result, status is 404.
            // Note: By a design choice, get requests to unauthorized pages return a 404, not a 401;
            // This is intentional as it obfuscates resources that the user isn't explicity authorized to access.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
        }
        
        return $this->dataObjects;
    }
}