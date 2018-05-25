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

    /**
     * Validates path name as set in resourceData.
     * @param int $ancestorPageId: ID of the page to potentially add a child with the pathname.
     * @param int $pageId: Optional. If set, disregard a page with the same pathname; assume we are operating on that existing page.
     * @return bool t/f path name is valid and doesn't conflict with an existing one.
     */
    function validatePathName($ancestorPageId, $pageId = null) {

        $pathNameValid = false;
        do {
            $conn = $this->pageViewReference->getSqlConn();
            // Pathname contains disallowed characters?
            if (!ctype_alnum(preg_replace('[_|-]', '', $this->resourceData['pathName']))) {
                // if the supplied path name contains non-alphanumeric chars other than underscore,
                // invalidate the request.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName may only contain alphanumeric characters or underscores.';
                break;
            }
            // Pathname is empty?
            if (empty($this->resourceData['pathName'])) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName must not be empty.';
                break;
            }

            // Pathname already exists for ancestor page?
            $sql = <<<SQL
            SELECT p.pathname
            FROM page_hierarchy_bridge phb
            JOIN pages p ON phb.descendant_page_id = p.page_id
            WHERE phb.page_id = :ancestorPageId
            AND pathname = :pathName
            AND (phb.descendant_page_id != :pageId OR :pageId IS NULL)
SQL;
            $sqlParams = array(
                 'ancestorPageId' => $ancestorPageId
                ,'pathName' => $this->resourceData['pathName']
                ,'pageId' => $pageId
            );
            $stmt = $conn->prepare($sql);
            $stmt->execute($sqlParams);;
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName already exists. Choose a different page title, or try supplying the pathName explicitly.';
                break;
            }

            // Ancestor page doesn't exist?
            $sql = <<<SQL
            SELECT page_id
            FROM pages
            WHERE page_id = :ancestorPageId;
SQL;
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('ancestorPageId' => $ancestorPageId));
            $result = $stmt->fetchAll();
            if (empty($result)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'ancestorPageId refers to a page that does not exist.';
                break;
            }

            // Validation OK
            $pathNameValid = true;
        } while (false);

        return $pathNameValid;
    }

    function generatePathName() {
        /**
         * Generates a path name from the page title specified in resourceData.
         * @return string a valid pathname.
         */
        $conn = $this->pageViewReference->getSQLConn();
        // If it's not supplied, generate one from the title.
        $sql = "SELECT GENERATE_PATHNAME(:pageTitle)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('pageTitle' => $this->resourceData['pageTitle']));
        $result = $stmt->fetchall();
        $this->resourceData['pathName'] = $result[0][0];
        return $this->resourceData['pathName'];
    }


    function validatePageBuilderClass() {
        /**
         * Attempts to instantiate the PageBuilder class specified in resourceData.
         * @var string $pageBuilderClass
         * @return bool t/f, the named class can be instantiated.
         */
        $pageBuilderClass = $this->resourceData['pageBuilderClass'];

        if (!class_exists($pageBuilderClass)) {

            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Error: Invalid Page builder class: ' . $pageBuilderClass;
            return false;
        } else {
            return true;
        }

    }


    /**
     * Attempts to instantiate the PageView class specified in resourceData.
     * @var string $pageViewClass
     * @return bool t/f, the named class can be instantiated.
     */
    function validatePageViewClass() {
        // If invalid, update HTTP status and message.
        $pageViewClass = $this->resourceData['pageViewClass'];

        if (!class_exists($pageViewClass)) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Error: Invalid PageView class: ' . $pageViewClass;
            return false;
        } else {
            return true;
        }

    }

    /**
     * Performs a database lookup to check whether the page type of the request is valid.
     * @return bool $pageTypeValid - T/F page type exists.
     */
    function validatePageTypeId() {

        $pageTypeValid = false;
        $conn = $this->pageViewReference->getSqlConn();
        $sql = "SELECT pagetype_id FROM pagetypes WHERE pagetype_id = :pageTypeId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageTypeId' => $this->resourceData['pageTypeId']]);
        $result = $stmt->fetchAll();
        if ($result) {
            $pageTypeValid = true;
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Error: Invalid pageTypeId: ' . strval($this->resourceData['pageTypeId']);
        }

        return $pageTypeValid;
    }


    /**
     * Recursively tests whether a user ID has write access to a page and all of its children,
     * @param int $userId - User ID to be tested.
     * @param int $pageId - The page ID where we start.
     * @return bool $userHasAccess - t/f, user has write access to this page and all its children.
     */
    function recursiveCheckWriteAccess($userId, $pageId) {

        $conn = $this->pageViewReference->getSqlConn();
        // Can the user access the current page?
        $userHasAccess = CheckPageUserAccess::checkUserAccess($userId, $pageId, $conn, true);
        // If yes, recurse, checking any children of the page.
        if ($userHasAccess) {
            $sql = "SELECT descendant_page_id FROM page_hierarchy_bridge WHERE page_id = :pageId";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['pageId' => $pageId]);
            $result = $stmt->fetchAll();
            // If the page has children, recurse.
            foreach ($result as $row) {
                $childPageId = $row[0];
                // Recurse here to test each child page.
                $userHasAccess = $this->recursiveCheckWriteAccess($userId, $childPageId);
                if (!$userHasAccess) {
                    // If we find a page where user doesn't have write access, break the loop and stop recursion.
                    break;
                }
            }
        }

        return $userHasAccess;
    }


    /**
     * Instantiate an APIDataValidator outside PostAction so it isn't inherited.
     */
    function instantiatePostValidator() {
        $this->apiDataValidator = new PagePostApiDataValidator();
    }


    /**
     * Instantiate an APIDataValidator outside PutAction so it isn't inherited.
     */
    function instantiatePutValidator() {
        $this->apiDataValidator = new PagePutApiDataValidator();
    }


    /**
     * Called by abstract ApiResource::getResource.
     * Performs authentication, validation and execution of a POST request.
     * @return object (array), $this->resourceData
     */
    function postAction() {

        $conn = $this->pageViewReference->getSQLConn();

        $this->instantiatePostValidator();

        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);

        // Set defaults.
        if (!isset($this->resourceData['pageLinkText']) && isset($this->resourceData['pageTitle']))
            $this->resourceData['pageLinkText'] = $this->resourceData['pageTitle'];

        if (!isset($this->resourceData['redirectOnError']))
            $this->resourceData['redirectOnError'] = false;

        if (!isset($this->resourceData['published']))
            $this->resourceData['published'] = false;

        if (!isset($this->resourceData['pageTypeId']))
            $this->resourceData['pageTypeId'] = 0;

        // begin validation sequence
        do {
            $userId = $this->authenticateUser();
            if (empty($userId)) {
                // Authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                $this->statusMessage = 'Access denied. Go away.';
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Validate input.
            if (!$this->validateData($this->resourceData)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->resourceData = array('status' => $this->statusMessage);
                break;

            }

            // Is the ancestor page valid, and does the user have write access?
            $userHasAccess = CheckPageUserAccess::checkUserAccess($userId, $this->resourceData['ancestorPageId'], $conn, true);
            if (!$userHasAccess) {
                // No access or page doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Generate the path name if not supplied explicitly
            if (!isset($this->resourceData['pathName']))
                $this->generatePathName();

            // Now validate the path name
            if (!$this->validatePathName($this->resourceData['ancestorPageId'])) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Validate PageBuilder class
            if (!$this->validatePageBuilderClass()) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Validate PageView class
            if (!$this->validatePageViewClass()) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Validate pageTypeId
            if (!$this->validatePageTypeId()) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Attempt the page insert
            $sql = <<<SQL
            SELECT CREATE_PAGE(
                 :parentPageId
                ,:pathName
                ,:pageTitle
                ,:pageLinkText
                ,:pageBuilderClass
                ,:pageViewClass
                ,:redirectOnError
                ,:published
                ,:pageTypeId
            )
SQL;
            $sqlParams = array(
                 'parentPageId' => $this->resourceData['ancestorPageId']
                ,'pathName' => $this->resourceData['pathName']
                ,'pageTitle' => $this->resourceData['pageTitle']
                ,'pageLinkText' => $this->resourceData['pageLinkText']
                ,'pageBuilderClass' => $this->resourceData['pageBuilderClass']
                ,'pageViewClass' => $this->resourceData['pageViewClass']
                ,'redirectOnError' => intval($this->resourceData['redirectOnError'])
                ,'published' => intval($this->resourceData['published'])
                ,'pageTypeId' => $this->resourceData['pageTypeId']
            );

            $pageId = null;
            $stmt = $conn->prepare($sql);

            try {
                $stmt->execute($sqlParams);
                $result = $stmt->fetchall();
                $pageId = $result[0][0];
            } catch (PDOException $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of page in database failed due to database error: ' . $e->getMessage();
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Check to ensure page ID was actually created
            if (!$pageId) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Page creation failed, possible due to a silent database error. Debug the data supplied to PageDataResource.';
                break;
            }

            // If the user is a non-admin user, create a record in page_user_access.
            $sql = <<<SQL
            INSERT INTO page_user_access
                (page_id, user_id, can_edit)
                SELECT
                     :pageId
                    ,:userId
                    ,1
                FROM users u
                WHERE u.user_id = :userId AND u.is_admin = 0

SQL;
            $stmt = $conn->prepare($sql);
            $sqlParams = array('pageId' => $pageId, 'userId' => $userId);
            $stmt->execute($sqlParams);

            // Success. Clear resource data and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($pageId);
            $this->getAction();
            $this->httpStatus = Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse');
        } while(false);

        return $this->resourceData;

    }


    /**
     * Performs authentication, validation and execution of a PUT request.
     * @return array
     */
    function putAction() {

        $this->instantiatePutValidator();

        // Connect to SQL
        $conn = $this->pageViewReference->getSQLConn();
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
            if (empty($pageId)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'PUT requests require the parameter "id" in the query string to specify a resource to be updated.';
                break;
            }

            // Validate input.
            if (!$this->validateData($this->resourceData)) {
                // Not valid? Respond with status message
                // HTTP status would already be set by the validateData method inherited from DataResource
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Is the page valid, and does the user have write access?
            $userHasAccess = CheckPageUserAccess::checkUserAccess($userId, $pageId, $conn, true);
            if (!$userHasAccess) {
                // No access or page doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // If supplied, is the path name valid?
            if (isset($this->resourceData['pathName'])) {
                $parentPageId = GrabParentPageId::getParentId($pageId, $conn);
                if (!$this->validatePathName($parentPageId, $pageId)) {
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }

            // If supplied, is the page type ID valid?
            if (isset($this->resourceData['pageTypeId'])) {
                if (!$this->validatePageTypeId()) {
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }

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
                $sqlParams['redirectOnError'] = intval($this->resourceData['redirectOnError']);
            }
            // published
            if(isset($this->resourceData['published'])) {
                array_push($updateFields, 'published = :published');
                $sqlParams['published'] = $this->resourceData['published'];
            }
            // pagetype id
            if(isset($this->resourceData['pageTypeId'])) {
                array_push($updateFields, 'pagetype_id = :pageTypeId');
                $sqlParams['pageTypeId'] = $this->resourceData['pageTypeId'];
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

            if (!empty($updateFields)) {
                try {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($sqlParams);
                } catch (PDOException $e) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                    $this->statusMessage = $e->getMessage();
                    break;
                }
            }
            // Success. Clear resourceData and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($pageId);
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->getAction();

        } while (false);

        return $this->resourceData;

    }


    /**
     * Called by abstract ApiResource::getResource.
     * Performs authentication, validation and execution of a GET request.
     * @return object (array), $this->resourceData
     */
    function getAction() {

        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $pageId = $this->validateIntParameter('id');
        $conn = $this->pageViewReference->getSQLConn();

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
                ,p.created_dt
                ,p.modified_dt
                ,redirect_on_error
                ,published
                ,pagetype_id
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
            $stmt = $conn->prepare($sql);
            $sqlParams = array('userId' => $userId, 'pageId' => $pageId);
            $stmt->execute($sqlParams);
            $result = $stmt->fetchAll();
        }

        // Process the response
        if (count($result) > 0) {
            foreach ($result as $row)
                $this->resourceData[$row[0]] = array(
                     'url' => $this->resourceUrl . '?id=' . strval($row['page_id'])
                    ,'pageUri' => GrabPageURL::getURL($row['page_id'], $conn)
                    ,'ancestorPageId' => intval($row['ancestor_page_id'])
                    ,'pathName' => $row['pathname']
                    ,'pageTitle' => $row['page_title']
                    ,'pageLinkText' => $row['page_link_text']
                    ,'pageBuilderClass' => $row['pagebuilder_class']
                    ,'pageViewClass' => $row['pageview_class']
                    ,'createdDate' => $row['created_dt']
                    ,'modifiedDate' => $row['modified_dt']
                    ,'redirectOnError' => boolval($row['redirect_on_error'])
                    ,'published' => boolval($row['published'])
                    ,'pageTypeId' => intval($row['pagetype_id'])
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


    /**
     * Performs authentication, validation and execution of a DELETE request.
     * hard-deletes the page and any of its children.
     * @return array
     */
    function deleteAction() {


        $pageId = $this->validateIntParameter('id');
        $conn = $this->pageViewReference->getSQLConn();
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

            // if user is not an admin...
            if (!$this->sessionManager->userIsAdmin) {
                // Check whether user has write access to this page and ALL its children (since deletion is also recursive).
                $userHasAccess = $this->recursiveCheckWriteAccess($userId, $pageId);
                if (!$userHasAccess) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                    $this->statusMessage = 'DELETE access denied; you do not have write access to this page or one of its children.';
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }

            // Validation has passed; delete the page.
            // Note: sp_delete_page is recursive; it also deletes any children the page has.
            $sql = "CALL sp_delete_page(:pageId)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('pageId' => $pageId));
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');

        } while(false);

        return $this->resourceData;
    }
}
