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
    
    function buildFields() {
        // Define the sub-resources of this resource.
        
        $pathName = new StringFieldValidator();
        $pathName->maxLength = 50;
        $pathName->allowNull=true;
        $this->fields['pathName'] = $pathName;
        
        $title = new StringFieldValidator();
        $title->maxLength = 50;
        $this->fields['title'] = $title;
        
        $body = new StringFieldValidator();
        $this->fields['body'] = $body;
        
        $blogID = new IntegerFieldValidator();
        $this->fields['blogID'] = $blogID;
        
        # todo: make a boolean field validator??
        $published = new IntegerFieldValidator();
        $this->fields['published'] = $published;
    }

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
    
    function postAction() {
        // Responds to POST requests creating a new blog post.

        // Set up field validators
        $this->buildFields();
        
        $sqlConn = $this->pageViewReference->getSQLConn();
        // Acquire the POST body (if not already set)
        if (count($this->dataObjects) == 0)
            $this->dataObjects = json_decode(file_get_contents("php://input"), true);
            
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();
            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Validate fields in post data for data type
            if (!$this->validateData($this->dataObjects)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Check that the user has access to the ancestor page (and that it exists).
            $sql = <<<SQL
            SELECT
                 p.page_id
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                b.blog_id = :blogID
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
            )
SQL;
            $blogID = $this->dataObjects['blogID'];
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogID' => $blogID));
            $result = $stmt->fetchall();
            
            if (!$result) {
                // No access or ancestor doesn't exist? Return a 400 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Ancestor page ID not found';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Acquire the page ID of the blog
            $blogPageID = $result[0][0];
            
            // Generate pathname from the title
            $sql = "SELECT GENERATE_PATHNAME(:title)";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('title' => $this->dataObjects['title']));
            $result = $stmt->fetchall();
            $this->dataObjects['pathName'] = $result[0][0];
            
            $sql = <<<SQL
            SELECT CREATE_BLOG_POST (
                 :blogPageID            -- param_page_id BIGINT
                ,:userID                -- param_user_id BIGINT
                ,:title                 -- param_title VARCHAR(50)
                ,:body                  -- param_body TEXT
                ,'BlogPostSinglePageBuilder'  -- param_pagebuilder_class VARCHAR(50)
                ,''                     -- param_thumbnail_image_vector VARCHAR(50) (note: not implemented)
            )
SQL;
            // Because the SQL function doesn't currently match the API input,
            // we need to make an independent array of parameters
            $sqlParams = array(
                'blogPageID' => $blogPageID,
                'userID' => $userID,
                'title' => $this->dataObjects['title'],
                'body' => $this->dataObjects['body'],
            );
            

            $stmt = $sqlConn->prepare($sql);
            $stmt->execute($sqlParams);
            $result = $stmt->fetchall();
            $blogPostPageID = $result[0][0];

            if (!$blogPostPageID) {
                // If this failed, it's probably because a child with that pathname already exists.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of blog in database failed, possibly due to duplicate blog post title or other database error. Try a different title.';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }

            
            // Query the database for the BlogPostID, because goddammit
            $sql = "SELECT blog_post_id FROM blog_posts WHERE page_id = :pageID";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('pageID' => $blogPostPageID));
            $result = $stmt->fetchAll();
            $blogPostID = $result[0][0];
            
            // Return the newly created blog post.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->parameters['id'] = $blogPostID;
            $this->dataObjects = array();
            $this->dataObjects = $this->getAction();
            
        } while (false);
        
        return $this->dataObjects;
    }
    
    function putAction() {
        // Responds to PUT requests to update blog posts.
        
        // Build and modify the fields array
        $this->buildFields();
        $this->fields['blogID']->allowNull = true;
        $this->fields['title']->allowNull = true;
        $this->fields['body']->allowNull = true;
        $this->fields['published']->allowNull = true;
        
        // The id parameter is required - Validate it.
        $blogPostID = $this->validateIntParameter('id');
        
        $sqlConn = $this->pageViewReference->getSQLConn();
        // Acquire the POST body (if not already set)
        if (count($this->dataObjects) == 0)
            $this->dataObjects = json_decode(file_get_contents("php://input"), true);
            
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();
            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
                
            // Validate fields in post data for data type
            if (!$this->validateData($this->dataObjects)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Verify that the blog post exists, and that the user has access.
            $sql = <<<SQL
            SELECT
                 p.page_id
                ,b.title
                ,b.body
                ,b.published
            FROM blog_posts b
            JOIN pages p ON b.page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                b.blog_post_id = :blogPostID
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogPostID' => $blogPostID));
            $result = $stmt->fetchall();
            
            if (!$result) {
                // No access or blog doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Get result fields.
            $resultPageID = $result[0][0];
            $resultTitle = $result[0][1];
            $resultBody = $result[0][2];
            $resultPublished = $result[0][3];

            // If any of the fields was not included in the request body, add it from the existing record.
            if (!array_key_exists('title', $this->dataObjects))
                $this->dataObjects['title'] = $resultTitle;
            
            if (!array_key_exists('body', $this->dataObjects)) 
                $this->dataObjects['body'] = $resultBody;
            
            if (!array_key_exists('published', $this->dataObjects))
                $this->dataObjects['published'] = $resultPublished;

            // Add the BlogPostID to data objects.
            $this->dataObjects['blogPostID'] = $blogPostID;

            // this requires 2 queries.
            // First: the blog post record.
            $sql = <<<SQL
            UPDATE blog_posts
            SET
                 title = :title
                ,body = :body
                ,published = :published
            WHERE blog_post_id = :blogPostID;
SQL;
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute($this->dataObjects);

            // Second: the page record.
            $sql = "UPDATE pages SET page_title = :title WHERE page_id = :pageID";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('title' => $this->dataObjects['title'], 'pageID' => $resultPageID));
            
            // woot!
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            
        } while (false);
        
        // Clear the data array and eturn the updated record.
        $this->dataObjects = array();
        return $this->getAction();
    }

    function deleteAction() {
        // Responds to a DELETE request to delete a resouce.
        // Performs a "soft delete" of the page specified in request parameters.
        
        // Connect to SQL
        $sqlConn = $this->pageViewReference->getSQLConn();
        
        // The blogID should be set in the URL parameters.
        $blogPostID = $this->validateIntParameter('id');
        
        
        // Go through authentication/validation sequence
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();
            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Check that the user has access to the blog page (and that it exists).
            $sql = <<<SQL
            SELECT
                 p.page_id
            FROM blog_posts b
            JOIN pages p ON b.page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                b.blog_post_id = :blogPostID
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogPostID' => $blogPostID));
            $result = $stmt->fetchall();
            
            if (!$result) {
                // No access or blog doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }
            
            // So far so good - delete the page
            $pageID = $result[0][0];
            // Note: sp_delete_page is recursive; it also soft-deletes any children the page has.
            $sql = "CALL sp_delete_page(:pageID)";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('pageID' => $pageID));
            
            // set up the response.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');
            $this->dataObjects = array();
            
        } while (false);
        
        return $this->dataObjects;
        
    }
}