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
                ,p.name
                ,b.description
                ,p.page_id
                ,phb.page_id AS ancestor_page_id
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            -- 1st join to PHB is to get the parent page ID
            LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
            -- 2nd join to PHB is to get any children
            LEFT JOIN page_hierarchy_bridge phb2 ON p.page_id = phb2.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
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
            // TODO: fix query so it doesn't include children
            $lastID = null;
            foreach ($result as $row) {
                // If the outer record has not repeated, create a 'blog' record in the array.
                if ($row['blog_id'] != $lastID) {
                    $blog = array(
                         'url' => $this->resourceURL . '?id=' . strval($row[0])
                        ,'pageURI' => GrabPageURL::getURL($row[3], $sqlConn)
                        ,'blogName' => $row[1]
                        ,'blogDescription' => $row[2]
                        ,'pageID' => $row[3]
                        ,'ancestorPageID' => $row[4]
                        ,'blogPosts' => array()
                    );
                }
                $this->dataObjects[$row[0]] = $blog;

                // If the row contains a "child" (AKA blog post), append it to the blog record.
                $blogPostResource = new BlogPostDataResource($this->pageViewReference);
                $blogPostResource->resourceURI = 'coreservices/blogposts/';
                $blogPostResource->parameters = array('blog_id' => $row[0]);
                $blogPost = $blogPostResource->getResource();

                // Append the blog post record to the blog record
                if ($blogPost)
                    array_push($this->dataObjects[$row[0]]['blogPosts'], $blogPost);

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
        // Validates as POST request and responds.
        
        // set up the field validators.
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
            FROM pages p
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                p.page_id = :pageID 
                AND
                (
                    (p.published = 1 AND p.deleted IS NULL)
                    OR
                    pua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
SQL;
            $ancestorPageID = $this->dataObjects['ancestorPageID'];
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'pageID' => $ancestorPageID));
            $result = $stmt->fetchall();

            if (!$result) {
                // No access or ancestor doesn't exist? Return a 400 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Ancestor page ID not found';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }
            
            // Next: Validate pathname, generate one from the title if not explicitly supplied.
            if (!array_key_exists('pathName', $this->dataObjects)) {
                // If it's not supplied, generate one from the title.
                $sql = "SELECT GENERATE_PATHNAME(:blogName)";
                $stmt = $sqlConn->prepare($sql);
                $stmt->execute(array('blogName' => $this->dataObjects['blogName']));
                $result = $stmt->fetchall();
                $this->dataObjects['pathName'] = $result[0][0];
            } else if (!ctype_alnum(str_replace('_', '', $this->dataObjects['pathName']))) {
                // Otherwise, if the supplied path name contains non-alphanumeric chars other than underscore,
                // invalidate the request.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName may only contain alphanumeric characters or underscores.';
                $this->dataObjects = array('status' => $this->statusMessage);
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

            try {
                $stmt = $sqlConn->prepare($sql);
                $stmt->execute($this->dataObjects);
                $result = $stmt->fetchall();
                $blogID = $result[0][0];
            } catch (PDOException $e) {
                // If this failed, it's probably because a child with that pathname already exists.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of blog in database failed, possibly due to duplicate pathname or other database error. Try changing the title or supplying the pathName explicitly.';
                $this->dataObjects = array('status' => $this->statusMessage);
                break;
            }         

            // Return the newly created blog.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->parameters['id'] = $blogID;
            $this->dataObjects = array();
            $this->dataObjects = $this->getAction(); 

        } while (false);
        
        return $this->dataObjects;
    }
    
    function putResource($putData) {
        // Validate fields in PUT data for data type
        // Update existing resource
    }
    
    function deleteResource() {
        // Delete resource
    }
    
    
}

