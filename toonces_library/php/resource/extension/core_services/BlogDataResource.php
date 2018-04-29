<?php
/*
 * BlogDataResource.php
 * Initial Commit: Paul Anderson, 4/10/2018
 * 
 * A DataResource class definining the inputs and outputs for a Blogs endpoint.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogDataResource extends DataResource implements iResource {
    

    function buildFields() {
        // Define the sub-resources of this resource.

        $pathName = new StringFieldValidator();
        $pathName->maxLength = 50;
        $pathName->allowNull=true;
        $this->fields['pathName'] = $pathName;

        $blogName = new StringFieldValidator();
        $blogName->maxLength = 50;
        $this->fields['blogName'] = $blogName;
        
        $ancestorPageID = new IntegerFieldValidator();
        $this->fields['ancestorPageID'] = $ancestorPageID;
        
    }
    
    function getAction() {
        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $blogID = $this->validateIntParameter('id');
        $sqlConn = $this->pageViewReference->getSQLConn();
        
        // Acquire the user id if this is an authenticated request.
        $userID = $this->authenticateUser() ?? 0;
        // Build the query
        $sql = <<<SQL
            SELECT
                 b.blog_id
                ,p.page_title
                ,b.description
                ,p.page_id
                ,phb.page_id AS ancestor_page_id
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            -- 1st join to PHB is to get the parent page ID
            LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON u.user_id = :userID
            WHERE
                (b.blog_id = :blogID OR :blogID IS NULL) 
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
        
        if (count($result) > 0) {
            foreach ($result as $row) {
                // If the outer record has not repeated, create a 'blog' record in the array.
                $blog = array(
                     'url' => $this->resourceUrl . '?id=' . strval($row[0])
                    ,'pageURI' => GrabPageURL::getURL($row[3], $sqlConn)
                    ,'blogName' => $row[1]
                    ,'blogDescription' => $row[2]
                    ,'pageID' => $row[3]
                    ,'ancestorPageID' => $row[4]
                    ,'blogPosts' => array()
                );

                $this->resourceData[$row[0]] = $blog;

                $blogPostResource = new BlogPostDataResource($this->pageViewReference);
                $blogPostResource->resourceUri = 'coreservices/blogposts/';
                $blogPostResource->parameters = array('blog_id' => $row[0]);
                $blogPostResource->httpMethod = 'GET';
                $blogPost = $blogPostResource->getResource();

                // Append the blog post record to the blog record
                if ($blogPost)
                    array_push($this->resourceData[$row[0]]['blogPosts'], $blogPost);

            }
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            // If no result, status is 404.
            // Note: By a design choice, get requests to unauthorized pages return a 404, not a 401;
            // This is intentional as it obfuscates resources that the user isn't explicity authorized to access.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
        }

        return $this->resourceData;
    }


    function postAction() {
        // Validates as POST request and responds.

        // set up the field validators.
        $this->buildFields();

        $sqlConn = $this->pageViewReference->getSQLConn();
        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();

            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // Validate fields in post data for data type        
            if (!$this->validateData($this->resourceData)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Check that the user has access to the ancestor page (and that it exists). 
            // Note: A user must be an admin or explicitly given acces to the ancestor page in order to create a blog.
            $sql = <<<SQL
            SELECT
                 p.page_id
            FROM pages p
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON u.user_id = :userID
            WHERE
                p.page_id = :pageID 
                AND
                (
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;
            $ancestorPageID = $this->resourceData['ancestorPageID'];
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'pageID' => $ancestorPageID));
            $result = $stmt->fetchall();
            if (!$result) {
                // No access or ancestor doesn't exist? Return a 400 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Ancestor page ID not found';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // Next: Validate pathname, generate one from the title if not explicitly supplied.
            if (!array_key_exists('pathName', $this->resourceData)) {
                // If it's not supplied, generate one from the title.
                $sql = "SELECT GENERATE_PATHNAME(:blogName)";
                $stmt = $sqlConn->prepare($sql);
                $stmt->execute(array('blogName' => $this->resourceData['blogName']));
                $result = $stmt->fetchall();
                $this->resourceData['pathName'] = $result[0][0];
            } else if (!ctype_alnum(str_replace('_', '', $this->resourceData['pathName']))) {
                // Otherwise, if the supplied path name contains non-alphanumeric chars other than underscore,
                // invalidate the request.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName may only contain alphanumeric characters or underscores.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
 
            $sql = <<<SQL
            SELECT CREATE_BLOG (
                 :ancestorPageID        -- parent_page_id BIGINT
                ,:pathName              -- blog_url_name VARCHAR(50)
                ,:blogName              -- blog_display_name VARCHAR(100)
                ,'BlogPageBuilder'      -- blog_pagebuilder_class VARCHAR(50)
                ,'HTMLPageView'         -- blog_pageview_class VARCHAR(50)
            )
SQL;
            
            $blogID = null;
            try {
                $stmt = $sqlConn->prepare($sql);
                $sqlParams = array(
                     'ancestorPageID' => $this->resourceData['ancestorPageID']
                    ,'pathName' => $this->resourceData['pathName']
                    ,'blogName'=> $this->resourceData['blogName']
                );
                $stmt->execute($sqlParams);
                $result = $stmt->fetchall();
                $blogID = $result[0][0];

            } catch (PDOException $e) {
                // If this failed, it's probably because a child with that pathname already exists.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of blog in database failed, possibly due to duplicate pathname or other database error. Try changing the title or supplying the pathName explicitly.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }         
            
            // Check to ensure blog ID was actually created'
            if (!$blogID) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Blog creation failed, possibly due to a dupliate pathName. Try changing the or supplying the pathName explicitly.';
                break;
            }
            // Return the newly created blog.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->parameters['id'] = strval($blogID);
            $this->resourceData = array();
            $this->resourceData = $this->getAction(); 

        } while (false);
        
        return $this->resourceData;
    }


    function putAction() {
        // Responds to a PUT request to update an existing blog resource.
        // Validate fields in PUT data for data type
        // set up the field validators.
        $this->buildFields();
        // allow the ancestor page ID field to be null.
        $this->fields['ancestorPageID']->allowNull = true;

        // Connect to SQL
        $sqlConn = $this->pageViewReference->getSQLConn();
        // The blogID should be set in the URL parameters.
        $blogID = $this->validateIntParameter('id');
        // Acquire the PUT body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        
        // Go through authentication/validation sequence
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();
            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Reject the PUT if the 'id' parameter is not set.
            if (!isset($blogID)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'PUT requests require the parameter "id" in the query string to specify a blog to be updated.';
                break;
            }

            // Validate fields in PUT data for data type
            if (!$this->validateData($this->resourceData)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
           
            // Check that the user has access to the blog page (and that it exists).
            $sql = <<<SQL
            SELECT
                 p.page_id
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON u.user_id = :userID
            WHERE
                b.blog_id = :blogID
                AND
                (
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;

            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogID' => $blogID));
            $result = $stmt->fetchall();
            if (!$result) {
                // No access or blog doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }
            
            // So far so good - Update the blog's info.
            $pageID = $result[0][0];
            $sql = "UPDATE pages SET page_title = :blogName WHERE page_id = :pageID";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('blogName' => $this->resourceData['blogName'], 'pageID' => $pageID));
            
            // return the updated blog record.
            // Return the newly created blog.

            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->parameters['id'] = strval($blogID);
            $this->resourceData = array();
            $this->resourceData = $this->getAction(); 
            
        } while (false);
        
        return $this->resourceData;
    }

    function deleteAction() {
        // Responds to a DELETE request to delete a resouce.
        // Performs a "soft delete" of the page specified in request parameters.

        // Connect to SQL
        $sqlConn = $this->pageViewReference->getSQLConn();
        
        // The blogID should be set in the URL parameters.
        $blogID = $this->validateIntParameter('id');

        
        // Go through authentication/validation sequence
        do {
            // Authenticate the user.
            $userID = $this->authenticateUser();
            if (empty($userID)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // id parameter must be set - reject if not.
            if (!isset($blogID)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'DELETE requests require the parameter "id" in the query string to specify a blog to be deleted.';
                break;
            }

            // Check that the user has access to the blog page (and that it exists).
            $sql = <<<SQL
            SELECT
                 p.page_id
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON u.user_id = :userID
            WHERE
                b.blog_id = :blogID
                AND
                (
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'blogID' => $blogID));
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
            $this->resourceData = array();
            
        } while (false);

        return $this->resourceData;
            
    }
}

