<?php
/**
 * @author paulanderson
 * PageDataResource.php
 * Initial commit: Paul Anderson, 5/2/2018
 * 
 * DataResource subclass creating an API endpoint for managing pages.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class PageDataResource extends DataResource implements iResource {
    
    function buildFields() {
        $ancestorPageId = new IntegerFieldValidator();
        $this->fields['ancestorPageId'] = $parentPageId;
        
        $pathName = new StringFieldValidator();
        $pathName->maxLength = 50;
        $pathName->allowNull = true;
        $this->fields['pathName'] = $pathName;
        
        $pageTitle = new StringFieldValidator();
        $pageTitle->maxLength = 50;
        $this->fields['pageTitle'] = $pageTitle;
        
        $pageLinkText = new StringFieldValidator();
        $pageLinkText->maxLength = 50;
        // Defaults to title if not included
        $pageLinkText->allowNull = true;
        $this->fields['pageLinkText'] = $pageLinkText;
        
        $pageBuilderClass= new StringFieldValidator();
        $pageBuilderClass->maxLength = 50;
        $this->fields['pageBuilderClass'] = $pageBuilderClass;      
        
        $pageViewClass = new StringFieldValidator();
        $pageViewClass->maxLength = 50;
        $this->fields['pageViewClass'] = $pageViewClass;

        $redirectOnError = new BooleanFieldValidator();
        // Defaults to FALSE
        $redirectOnError->allowNull = true;
        $this->fields['redirectOnError'] = $redirectOnError;
        
        $published = new BooleanFieldValidator();
        $this->fields['published'] = $published;
        
        $pageTypeId = new IntegerFieldValidator();
        // Defaults to "general"
        $pageTypeId->allowNull = true;
        $this->fields['pageTypeId'] = $pageTypeId;

    }

    
    function validatePageInput($ancestorPageId = null, $pageId = null, $userId = null) {
        // Run through the page validation sequence.
        // RETURN: t/f, request is valid.
        
        $conn = $this->pageViewReference->getSQLConn();
        $requestValid = false;
        // Begin the validation sequence
        do {            
            // Validate fields in post data for data type
            if (!$this->validateData($this->resourceData)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // If required, check that the user has access to the ancestor page (and that it exists).
            // Note: A user must be an admin or explicitly given acces to the ancestor page in order to create a page.
            if($ancestorPageId) {
                $sql = <<<SQL
                SELECT
                     p.page_id
                FROM pages p
                LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userId)
                LEFT JOIN users u ON u.user_id = :userId
                WHERE
                    p.page_id = :pageId
                    AND
                    (
                        pua.user_id IS NOT NULL
                        OR
                        u.is_admin = TRUE
                    )
SQL;
                
                $stmt = $conn->prepare($sql);
                $stmt->execute(array('userId' => $userId, 'pageId' => $ancestorPageId));
                $result = $stmt->fetchall();
                if (!$result) {
                    // No access or ancestor doesn't exist? Return a 400 error.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                    $this->statusMessage = 'Ancestor page ID not found';
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }
            
            // If pageId parameter was set, check that the page ID is valid and user has access.
            if ($pageId) {

                $sql = <<<SQL
                SELECT
                     p.page_id
                FROM pages p
                LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
                LEFT JOIN users u ON u.user_id = :userID
                WHERE
                    b.page_id = :pageId
                    AND
                    (
                        pua.user_id IS NOT NULL
                        OR
                        u.is_admin = TRUE
                    )
SQL;
                $stmt = $sqlConn->prepare($sql);
                $stmt->execute(array('userID' => $userId, 'pageId' => $pageId));
                $result = $stmt->fetchall();
                if (!$result) {
                    // No access or blog doesn't exist? Return a 404 error.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                    break;
                }
                
            } else {
                // Was a pathName not explicitly supplied?
                // Only generate a pathname if this is a POST request (i.e., pageId is not set)
                if (!array_key_exists('pathName', $this->resourceData)) {
                    // If it's not supplied, generate one from the title.
                    $sql = "SELECT GENERATE_PATHNAME(:pageTitle)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(array('pageTitle' => $this->resourceData['pageTitle']));
                    $result = $stmt->fetchall();
                    $this->resourceData['pathName'] = $result[0][0];
                }
            }

            // Validate the pathname if set.
            if (isset($this->resourceData['pathName'])) {
                if (!ctype_alnum(str_replace('_', '', $this->resourceData['pathName']))) {
                    // if the supplied path name contains non-alphanumeric chars other than underscore,
                    // invalidate the request.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                    $this->statusMessage = 'pathName may only contain alphanumeric characters or underscores.';
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }
            
            // Validate page type ID if set.
            if (isset($this->resourceData['pageTypeId'])) {
                $sql = "SELECT page_type_id FROM pagetypes WHERE page_type_id = :pageTypeId";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['pageTypeId' => $this->resourceData['pageTypeId']]);
                $result = $stmt->fetchAll();
                if (!$result) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                    $this->statusMessage = 'Invalid page type ID.';
                    break;
                }
            }
            
            $requestValid = true;
        } while (false);
    }
    
    
    function postAction() {

        $conn = $this->pageViewReference->getSQLConn();
        // Set up field validators
        $this->buildFields();
        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        
        do {
            $userId = $this->authenticateUser();
            if (empty($userId)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Call the validation function
            if (!$this->validatePageInput($this->resourceData['ancestorPageId'], null, $userId))
                break;
            
            // So far so good
            // Attempt the page insert
            $sql = <<<SQL
            SELECT CREATE_PAGE(
                 :parentPageId          -- parent_page_id BIGINT
                ,:pathname              -- pathname VARCHAR(50)
                ,:pageTitle             -- page_title VARCHAR(50)
                ,:pageLinkText          -- page_link_text VARCHAR(50)
                ,:pageBuilderClass      -- pagebuilder_class VARCHAR(50)
                ,:pageViewClass         -- pageview_class VARCHAR(50)
                ,:redirectOnError       -- redirect_on_error BOOL
                ,:published             -- published BOOL
                ,:pagetypeId            -- pagetype_id BIGINT
            )
SQL;
            $sqlParams = array(
                 'parentPageId' => $this->resourceData['ancestorPageId']
                ,'pathname' => $this->resourceData['pathname']
                ,'pageTitle' => $this->resourceData['pageTitle']
                ,'pageLinkText' => $this->resourceData['pageLinkText']
                ,'pageBuilderClass' => $this->resourceData['pageBuilderClass']
                ,'pageViewClass' => $this->resourceData['pageViewClass']
                ,'redirectOnError' => $this->resourceData['redirectOnError']
                ,'published' => $this->resourceData['published']
                ,'pagetypeId' => $this->resourceData['pagetypeId']
            );
            
            $pageId = null;
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($sqlParams);
                $result = $stmt->fetchall();
                $pageId = $result[0][0];
            } catch (PDOException $e) {
                // If this failed, it's probably because a child with that pathname already exists.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of page in database failed, possibly due to duplicate pathname or other database error. Try changing the title or supplying the pathName explicitly.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // Check to ensure page ID was actually created
            if (!$pageId) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Blog creation failed, possibly due to a dupliate pathName. Try changing the or supplying the pathName explicitly.';
                break;
            }
            
            // Success. Clear resource data and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($pageId);
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->getAction();
                
        } while(false);
        
        return $this->resourceData;
        
    }
    
    
    function putAction() {
        // Build fields
        $this->buildFields();
        $conn = $this->pageViewReference->getSqlConn();
        
        // Allow nulls on certain fields
        $this->fields['ancestorPageId']->allowNull = true;
        $this->fields['parentPageId']->allowNull = true;
        $this->fields['pageTitle']->allowNull = true;
        $this->fields['pagebuilderClass']->allowNull = true;
        $this->fields['pageviewClass']->allowNull = true;
        $this->fields['published']->allowNull = true;

        // Connect to SQL
        $sqlConn = $this->pageViewReference->getSQLConn();
        // The blogID should be set in the URL parameters.
        $pageId = $this->validateIntParameter('id');

        // Acquire the PUT body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
            
        // Go through authentication/validation sequence
        do {
            // Authenticate the user.
            $userId = $this->authenticateUser();
            if (empty($userId)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Reject the PUT if the 'id' parameter is not set.
            if (!isset($pageId)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'PUT requests require the parameter "id" in the query string to specify a resource to be updated.';
                break;
            }
            
            // Call the input validator
            if (!$this->validatePageInput(null, $pageId, $userId))
                break;
            
            // If all validation so far has passed, update the page and its associated records.
            // Build the SQL depending on the fields to be updated
            $updateFields = array();
            $sqlParams = array();
            // pathname
            if (isset($this->resourceData['pathName'])) {
                array_push($updateFields, 'pathname = :pathName');
                $sqlParams['pathName'] = $this->resourceData['pathName'];
            }
            // page title
            if (isset($this->resourceData['pageTitle'])) {
                array_push($updateFields, 'page_title = :pageTitle');
                $sqlParams['pageTitle'] = $this->resourceData['pageTitle'];                
            }
            // page link text
            if (isset($this->resourceData['pageLinkText'])) {
                array_push($updateFields, 'page_link_text = :pageLinkText');
                $sqlParams['pageLinkText'] = $this->resourceData['pageLinkText'];
            }
            // pagebuilder class
            if (isset($this->resourceData['pageBuilderClass'])) {
                array_push($updateFields, 'pagebuilder_class = :pageBuilderClass');
                $sqlParams['pageBuilderClass'] = $this->resourceData['pageBuilderClass'];
            }
            // pageview class
            if (isset($this->resourceData['pageViewClass'])) {
                array_push($updateFields, 'pageview_class = :pageViewClass');
                $sqlParams['pageViewClass'] = $this->resourceData['pageViewClass'];
            }
            // redirect on error
            if(isset($this->resourceData['redirectOnError'])) {
                array_push($updateFields, 'redirect_on_error = :redirectOnError');
                $sqlParams['redirectOnError'] = $this->resourceData['redirectOnError'];
            }
            // published
            if(isset($this->resourceData['published'])) {
                array_push($updateFields, 'published = :published');
                $sqlParams['published'] = $this->resourceData['published'];
            }
            // pagetype id
            if(isset($this->resourceData['pageTypeId'])) {
                array_push($updateFields, 'page_type_id = :pageTypeId');
                $sqlParams['pagetypeId'] = $this->resourceData['pageTypeId'];
            }
            
            // Invalidate the request if no fields are set.
            if (empty($updateFields)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'At least one field must be specified in a PUT request.';
                break;
            }
            
            // Add page ID parameter
            $sqlParams['pageId'] = $pageId;
            $updateFieldsStr = implode(PHP_EOL . ',', $updateFields);
            
            $sql = <<<SQL
            UPDATE pages p
            SET
                %s
            WHERE
                page_id = :pageId

SQL;
            $sql = sprintf($sql, $updateFieldsStr);
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($sqlParams);
            } catch (PDOException $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = $e->getMessage();
                break;
            }
            
            // Success. Clear resourceData and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($pageId);
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->getAction();
            
        } while (false);
        
        return $this->resourceData;
        
    }
    
    
    function getAction() {
        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $pageId = $this->validateIntParameter('id');
        $sqlConn = $this->pageViewReference->getSQLConn();
        
        // Acquire the user id if this is an authenticated request.
        $userId = $this->authenticateUser() ?? 0;
        // Build the query
        $sql = <<<SQL
            SELECT
                 p.page_id
                ,phb.page_id AS ancestor_page_id
                ,pathname
                ,page_title
                ,page_link_text
                ,pagebuilder_class
                ,pageview_class
                ,created_dt
                ,modified_dt
                ,redirect_on_error
                ,published
                ,pagetype_ID
            FROM pages p
            -- join to PHB is to get the parent page ID
            LEFT JOIN page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userId)
            LEFT JOIN users u ON u.user_id = :userId
            WHERE
                (p.page_id = :pageId OR :pageId IS NULL)
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
        if ($pageId !== 0) {
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('userId' => $userId, 'pageId' => $pageId));
            $result = $stmt->fetchAll();
        }
        
        // Process the response
        if (count($result) > 0) {
            foreach ($result as $row)
                $this->resourceData[$row[0]] = array(
                     'url' => $this->resourceUrl . '?id=' . strval($row[0])
                    ,'pageUri' => GrabPageURL::getURL($row[0], $conn)
                    ,'ancestorPageId' => $row[1]
                    ,'pathname' => $row[2]
                    ,'pageTitle' => $row[3]
                    ,'pageLinkText' => $row[4]
                    ,'pagebuilderClass' => $row[5]
                    ,'pageviewClass' => $row[6]
                    ,'createdDate' => $row[7]
                    ,'modifiedDate' => $row[8]
                    ,'redirectOnError' => $row[10]
                    ,'published' => $row[11]
                    ,'pagetypeId' => $row[12]
                );
        
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            // If no result, status is 404.
            // Note: By a design choice, get requests to unauthorized pages return a 404, not a 401;
            // This is intentional as it obfuscates resources that the user isn't explicity authorized to access.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
        }
        
        return $this->resourceData;
    }

    
    function deleteAction() {
        // hard-deletes the page and any of its children.
        $pageId = $this->validateIntParameter('id');
        $sqlConn = $this->pageViewReference->getSQLConn();
        $this->resourceData = array();
        
        do {
            // Authenticate the user.
            $userId = $this->authenticateUser();
            if (empty($userId)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }
            
            // Reject the DELETE if the 'id' parameter is not set.
            if (!isset($pageId)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'DELETE requests require the parameter "id" in the query string to specify a resource to be deleted.';
                break;
            }
            
            // Make all input fields optional.
            foreach($this->fields as $field)
                $field->allowNull = true;
            
            // Call validatePageInput to check whether the page exists and user has access.
            if (!$this->validatePageInput(null, $pageId, $userId)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }
            
            // Validation has passed; delete the page.
            // Note: sp_delete_page is recursive; it also deletes any children the page has.
            $sql = "CALL sp_delete_page(:pageId)";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute(array('pageId' => $pageId));
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');
            
        } while(false);
        
        return $this->resourceData;
    }
}
 