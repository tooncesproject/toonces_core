<?php
/*
 * BlogsAPIPageBuilder.php
 * Initial commit: Paul Anderson, 3/9/2017
 * 
 * Generates a root resource for managing Toonces blogs.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogsAPIPageBuilder extends APIPageBuilder {
    
    var $apiDelegate;
    var $conn;
    
    function getAllBlogs($paramUserID) {
        // Query for all blogs the user has access to.
        $sql = <<<SQL

            SELECT
                 b.blog_id
                ,MIN(b.name)
                ,MIN(b.description)
                ,MIN(p.page_id)
                ,MIN(p.page_title)
                ,MIN(p.page_link_text)
                ,MIN(p.created_dt)
                ,MIN(p.modified_dt)
                ,MIN(phb.page_id) AS ancestor_page_id
                ,COUNT(phb2.page_id) AS post_count
            FROM blogs b
            JOIN pages p ON b.page_id = p.page_id
            -- 1st join to PHB is to get the parent page ID
            LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
            -- 2nd join to PHB is to get any children
            LEFT JOIN page_hierarchy_bridge phb2 ON p.page_id = phb2.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON pua.user_id = u.user_id
            WHERE
                (p.published = 1 AND p.deleted IS NULL)
                OR
                pua.user_id IS NOT NULL
                OR
                u.user_is_admin = TRUE
            GROUP BY b.blog_id
            ORDER BY b.blog_id ASC
SQL;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array('userID' => $userID));
        $result = $stmt->fetchAll();
        
        // Build the array to be returned if the SQL returns results.
        // Otherwise, return NULL
        $responseArray = NULL;

        if ($result) {
            // That is, if there is a result
            foreach ($result as $row) {
                $blogID = $row[0];
                $responseArray[$blogID] = array(
                     'blogName' => $row[1]
                    ,'blogDescription' => $row[2]
                    ,'pageID' => row[3]
                    ,'pageURI' => GrabPageURL::getURL($pageID, $this->conn)
                    ,'pageTitle' => $row[4]
                    ,'pageLinkText' => $row[5]
                    ,'createdDT' => $row[6]
                    ,'modifiedDT' => $row[7]
                    ,'ancestorPageID' => $row[8]
                    ,'blogPostCount' => $row[9]
                );
            }
        }
            
        return $responseArray;
        
    }
    
    function getRequestedBlog($paramUserID, $paramBlogID) {
        
        // Query the database for all the blog's posts.
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
                b.blod_id = :blogID
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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array('userID' => $userID, 'blogID' => $blogID));
        $result = $stmt->fetchAll();
        
        // Build the array for the DataResource.
        // Outer record is the page's metadata,
        // inner record is any children.
        // By default, if no result, the function returns NULL.
        $responseArray = NULL;
 
        if ($result) {
           $topRow = $result[0];
           // Outer record represents the Blog root page.
            $responseArray = array(
                 'blogID' => $topRow[0]
                ,'blogPageURI' => GrabPageURL::getURL($topRow[3], $this->conn)
                ,'blogName' => $topRow[1]
                ,'blogDescription' => $topRow[2]
                ,'blogPageID' => $topRow[3]
                ,'blogAncestorPageID' => $topRow[4]
            );
            
            // Inner records (blog posts) represent the blog's child pages.
            $blogPosts = array();
            if (!empty($result[5])) {
                // That is, if there are any.
                foreach ($result as $row) {
                    $blogPost = array(
                        ,'blogPostID' => $row[5]
                        ,'blogPostPageURI' => GrabPageURL::getURL($row[5], $this->conn)
                        ,'blogPostPageID' => $row[6]
                        ,'blogPostTitle' => $row[7]
                        ,'blogPostCreated' => $row[8]
                        ,'blogPostModified' => $row[9]
                        ,'pagePublished' => $row[10]
                        ,'pageDeleted' => $row[11]
                        ,'pageLinkText' => $row[12]  
                    );
                    array_push($blogPosts, $blogPost);
                }
            }
            $responseArray['blogPosts'] = $blogPosts;
        }
        
        return $responseArray;
      
    }
    
    function getAction($getParams) {
        
        // GET functionality:
        // Without blog_id GET parameter:
        //  Respond with a list of Blogs that exist on the site.
        // With blog_id GET parameter:
        //  Respond with a list of posts for the requested blog.
        
        // Authentication:
        //  Uses Toonces Admin authentication.
        //  Without authentication, respond with publihed blogs/posts only
        //  With authentication, respond with any blogs/posts the user is authorized to access.
        
        // Check user authentication
        $userIsAdmin = false;
        $userID = $this->apiDelegate->authenticateUser() ?? 0;
        if ($userID)
            $userIsAdmin = (int)$this->apiDelegate->sessionManager->userIsAdmin;

            
        $outputArray = NULL;
        
        if ($this->apiDelegate->validateHeaders()) {
            // Check for GET parameters
            $requestedBlogID = NULL;
            if (array_key_exists('blog_id', $getParams))
                $requestedBlogID = $getParams['blog_id'];
                

            if (is_numeric($requestedBlogID)) {
                $outputArray = $this->getRequestedBlog($userID, $requestedBlogID);        
            } else {
                $outputArray = $this->getAllBlogs($userID);
            }
        }

        if ($outputArray) {
            // Create a new DataResource object and populate the builder.
            $dataResource = new DataResource($this->pageViewReference);
            $dataResource->dataObjects = $outputArray;
            array_push($this->resourceArray, $dataResource);
        } else {
            // if $outputArray is null here, either the request headers or GET parameters failed validation.
            header('HTTP/1.1 500 Internal Server Error', true, 500);
        }
        
    }
    
    function postAction($postData) {
        // POST functionality:
        // Validate user authentication
        // Then validate input
        // If valid, create a new blog post with the content payload
        
        // User authentication:
        // Check user authentication
        $userIsAdmin = false;
        $userID = $this->apiDelegate->authenticateUser() ?? 0;
        if ($userID)
            $userIsAdmin = (int)$this->apiDelegate->sessionManager->userIsAdmin;

        $requestIsValid = false;
        $thumbnailImageVector = NULL;
        $responseArray = array();
        $errorMessage = NULL;
        // Validate the request.
        do {
            // headers
            if (!$this->apiDelegate->validateHeaders()) {
                $errorMessage = 'Hey yo, that\'s a shitty fucking API request. Try again.';
                break;
            }
            // Payload is well-formed JSON
            $payloadArray = json_decode($postData, true);
            if (!$payloadArray) {
                $errorMessage = 'Your JSON is hella wack.';
                break;
            }
            // Validate blogPageID
            if (!array_key_exists('blogPageID', $payloadArray)) {
                $errorMessage = 'Your JSON is hella wack.';
                break;
            }
            $sql = <<<SQL
                        SELECT b.page_id
                        FROM blogs b
                        JOIN pages p ON b.page_id = p.page_id
                        LEFT JOIN page_user_access pua ON p.page_id pua.page_id AND pua.user_id = :userID
                        WHERE p.page_id = :pageID
                            AND (pua.page_id IS NOT NULL OR :userIsAdmin = TRUE)
SQL;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array('userID' => $userID, 'pageID' => $payloadArray['blogPageID'], 'userIsAdmin' => $userIsAdmin));
            $result = $stmt->fetchAll();
            if (!$result[0][0]) {
                   $errorMessage = 'That blog page ID is bogus.';
                   break;
            }
            // validate thumbnailImageURI
            if (array_key_exists('thumbnailImageURI', $payloadArray)) {
                if (!filesize($payloadArray['thumbnailImageURI'])) {
                    $errorMessage = 'thumbnailImageURI file not found.';
                    break;
                }
            }
            
            // If we've reached this point, the request has passed validation
            $requestIsValid = true;
        } while (false);

        // If input is valid, go to town
        if ($requestIsValid) {
            // Insert the blog post
            $stmtParams = array(
                 'pageID' => $payloadArray['blogPageID']
                ,'userID' => $userID
                ,'title' => $payloadArray['title']
                ,'body' => $payloadArray['body']
                ,'thumbnailImageVector' => $thumbnailImageVector
            );

            $sql = <<<SQL
                SELECT CREATE_BLOG_POST (
                     :pageID                        --  param_page_id BIGINT
                    ,:userID                        --  param_user_id BIGINT
                    ,:title                         --  param_title VARCHAR(200)
                    ,:body                          --  param_body TEXT
                    ,'BlogPostSinglePageBuilder'    --  param_pagebuilder_class VARCHAR(50)
                    ,:thumbnailImageVector          --  param_thumbnail_image_vector VARCHAR(50)
                )
SQL;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            
            // Create the response array
            $newBlogPostPageID = $result[0];
            $responseArray = array(
                'blogPostPageID' => $newBlogPostPageID
               ,'title' => $payloadArray['title']
               ,'body' => $payloadArray['body']
               ,'thumbnailImageURI' => $thumbnailImageVector
            );


        } else {
            // No valid input? sorry bruh.
            header("HTTP/1.1 401 Unauthorized");
            $responseArray['errorCode'] = 401;
            $responseArray['errorMessage'] = $errorMessage;
        }
        
        // Create a new DataResource object and populate the builder.
        $dataResource = new DataResource($this->pageViewReference);
        $dataResource->dataObjects = $responseArray;
        array_push($this->resourceArray, $dataResource);
        
    }
}
