<?php
/**
 * @author paulanderson
 * ResourceDataResource.php
 * Initial commit: Paul Anderson, 5/2/2018
 *
 * DataResource subclass creating an API endpoint for managing pages.
 *
 */

require_once LIBPATH . 'php/toonces.php';

class ResourceDataResource extends DataResource implements iResource {

    /**
     * Validates path name as set in resourceData.
     * @param int $ancestorResourceId: ID of the resource to potentially add a child with the pathname.
     * @param int $resourceId: Optional. If set, disregard a resource with the same pathname; assume we are operating on that existing resource.
     * @return bool t/f path name is valid and doesn't conflict with an existing one.
     */
    function validatePathName($ancestorResourceId, $resourceId = null) {

        $this->connectSql();

        $pathNameValid = false;
        do {

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

            // Pathname already exists for ancestor resource?
            $sql = <<<SQL
            SELECT p.pathname
            FROM resource_hierarchy_bridge rhb
            JOIN resource p ON rhb.descendant_resource_id = p.resource_id
            WHERE rhb.resource_id = :ancestorResourceId
            AND pathname = :pathName
            AND (rhb.descendant_resource_id != :resourceId OR :resourceId IS NULL)
SQL;
            $sqlParams = array(
                 'ancestorResourceId' => $ancestorResourceId
                ,'pathName' => $this->resourceData['pathName']
                ,'resourceId' => $resourceId
            );
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($sqlParams);;
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'pathName already exists. Choose a different page title, or try supplying the pathName explicitly.';
                break;
            }

            // Ancestor page doesn't exist?
            $sql = <<<SQL
            SELECT resource_id
            FROM resource
            WHERE resource_id = :ancestorResourceId;
SQL;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array('ancestorResourceId' => $ancestorResourceId));
            $result = $stmt->fetchAll();
            if (empty($result)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'ancestorResourceId refers to a resource that does not exist.';
                break;
            }

            // Validation OK
            $pathNameValid = true;
        } while (false);

        return $pathNameValid;
    }

    /**
     * Generates a path name from the page title specified in resourceData.
     * @return string a valid pathname.
     */
    function generatePathName() {

        $this->connectSql();

        // If it's not supplied, generate one from the title.
        $sql = "SELECT GENERATE_PATHNAME(:pageTitle)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array('pageTitle' => $this->resourceData['pageTitle']));
        $result = $stmt->fetchall();
        $this->resourceData['pathName'] = $result[0][0];
        return $this->resourceData['pathName'];
    }


    /**
     * Attempts to instantiate the PageBuilder class specified in resourceData.
     * @var string $resourceClass
     * @return bool t/f, the named class can be instantiated.
     */
    function validateResourceClass() {

        $resourceClass = $this->resourceData['resourceClass'];

        if (!class_exists($resourceClass)) {

            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Error: Resource class: ' . $resourceClass;
            return false;
        } else {
            return true;
        }

    }


    /**
     * Recursively tests whether a user ID has write access to a page and all of its children,
     * @param int $userId - User ID to be tested.
     * @param int $resourceId - The page ID where we start.
     * @return bool $userHasAccess - t/f, user has write access to this page and all its children.
     */
    function recursiveCheckWriteAccess($userId, $resourceId) {

        $this->connectSql();

        // Can the user access the current page?
        $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $resourceId, $this->conn, true);
        // If yes, recurse, checking any children of the page.
        if ($userHasAccess) {
            $sql = "SELECT descendant_resource_id FROM resource_hierarchy_bridge WHERE resource_id = :resourceId";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['resourceId' => $resourceId]);
            $result = $stmt->fetchAll();
            // If the page has children, recurse.
            foreach ($result as $row) {
                $childResourceId = $row[0];
                // Recurse here to test each child page.
                $userHasAccess = $this->recursiveCheckWriteAccess($userId, $childResourceId);
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

        $this->connectSql();

        $this->instantiatePostValidator();

        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);

        if (!isset($this->resourceData['redirectOnError']))
            $this->resourceData['redirectOnError'] = false;

        if (!isset($this->resourceData['published']))
            $this->resourceData['published'] = false;

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
            $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $this->resourceData['ancestorResourceId'], $this->conn, true);
            if (!$userHasAccess) {
                // No access or page doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Generate the path name if not supplied explicitly
            if (!isset($this->resourceData['pathName']))
                $this->generatePathName();

            // Now validate the path name
            if (!$this->validatePathName($this->resourceData['ancestorResourceId'])) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Validate Resource class
            if (!$this->validateResourceClass()) {
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Attempt the page insert
            $sql = <<<SQL
            SELECT CREATE_RESOURCE(
                 :parentResourceId
                ,:pathName
                ,:resourceClass
                ,:redirectOnError
                ,:published
            )
SQL;
            $sqlParams = array(
                 'parentResourceId' => $this->resourceData['ancestorResourceId']
                ,'pathName' => $this->resourceData['pathName']
                ,'resourceClass' => $this->resourceData['resourceClass']
                ,'redirectOnError' => intval($this->resourceData['redirectOnError'])
                ,'published' => intval($this->resourceData['published'])
            );

            $resourceId = null;
            $stmt = $this->conn->prepare($sql);

            try {
                $stmt->execute($sqlParams);
                $result = $stmt->fetchall();
                $resourceId = $result[0][0];
            } catch (PDOException $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'Creation of page in database failed due to database error: ' . $e->getMessage();
                $this->resourceData = array('status' => $this->statusMessage);
                break;
            }

            // Check to ensure page ID was actually created
            if (!$resourceId) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Page creation failed, possible due to a silent database error. Debug the data supplied to ResourceDataResource.';
                break;
            }

            // If the user is a non-admin user, create a record in resource_user_access.
            $sql = <<<SQL
            INSERT INTO resource_user_access
                (resource_id, user_id, can_edit)
                SELECT
                     :resourceId
                    ,:userId
                    ,1
                FROM users u
                WHERE u.user_id = :userId AND u.is_admin = 0

SQL;
            $stmt = $this->conn->prepare($sql);
            $sqlParams = array('resourceId' => $resourceId, 'userId' => $userId);
            $stmt->execute($sqlParams);

            // Success. Clear resource data and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($resourceId);
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

        $this->connectSql();

        // The blogID should be set in the URL parameters.
        $resourceId = $this->validateIntParameter('id');

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
            if (empty($resourceId)) {
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
            $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $resourceId, $this->conn, true);
            if (!$userHasAccess) {
                // No access or page doesn't exist? Return a 404 error.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // If supplied, is the path name valid?
            if (isset($this->resourceData['pathName'])) {
                $parentResourceId = GrabParentResourceId::getParentId($resourceId, $this->conn);
                if (!$this->validatePathName($parentResourceId, $resourceId)) {
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
            // Resource class
            if (isset($this->resourceData['resourceClass'])) {
                array_push($updateFields, 'resource_class = :resourceClass');
                $sqlParams['pageBuilderClass'] = $this->resourceData['resourceClass'];
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

            // Add page ID parameter
            $sqlParams['resourceId'] = $resourceId;
            $updateFieldsStr = implode(PHP_EOL . ',', $updateFields);

            $sql = <<<SQL
            UPDATE resource
            SET
                %s
            WHERE
                resource_id = :resourceId

SQL;

            $sql = sprintf($sql, $updateFieldsStr);

            if (!empty($updateFields)) {
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute($sqlParams);
                } catch (PDOException $e) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                    $this->statusMessage = $e->getMessage();
                    break;
                }
            }
            // Success. Clear resourceData and call getAction().
            $this->resourceData = array();
            $this->parameters['id'] = strval($resourceId);
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->getAction();

        } while (false);

        return $this->resourceData;

    }


    /**
     * Called by abstract ApiResource::getResource.
     * Performs authentication, validation and execution of a GET request.
     * @return array
     */
    function getAction() {

        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $resourceId = $this->validateIntParameter('id');
        $this->connectSql();

        // Acquire the user id if this is an authenticated request.
        $userId = $this->authenticateUser() ?? 0;
        // Build the query
        $sql = <<<SQL
            SELECT
                 r.resource_id
                ,rhb.resource_id AS ancestor_resource_id
                ,pathname
                ,resource_class
                ,r.created_dt
                ,r.modified_dt
                ,r.redirect_on_error
                ,r.published
            FROM resource r
            -- join to PHB is to get the parent page ID
            LEFT JOIN resource_hierarchy_bridge rhb ON r.resource_id = rhb.descendant_resource_id
            LEFT JOIN resource_user_access rua ON r.resource_id = rua.resource_id AND (rua.user_id = :userId)
            LEFT JOIN users u ON u.user_id = :userId
            WHERE
                (r.resource_id = :resourceId OR :resourceId IS NULL)
                AND
                (
                    (r.published = 1 AND r.deleted IS NULL)
                    OR
                    rua.user_id IS NOT NULL
                    OR
                    u.is_admin = TRUE
                )
            ORDER BY r.resource_id ASC

SQL;
        // if the id parameter is 0, it's bogus. Only query if it's null or >= 1.
        $result = null;
        if ($resourceId !== 0) {
            $stmt = $this->conn->prepare($sql);
            $sqlParams = array('userId' => $userId, 'resourceId' => $resourceId);
            $stmt->execute($sqlParams);
            $result = $stmt->fetchAll();
        }

        // Process the response
        if (count($result) > 0) {
            foreach ($result as $row)
                $this->resourceData[$row[0]] = array(
                     'url' => $this->resourceUrl . '?id=' . strval($row['resource_id'])
                    ,'pageUri' => GrabResourceURL::getURL($row['resource_id'], $this->conn)
                    ,'ancestorResourceId' => intval($row['ancestor_resource_id'])
                    ,'pathName' => $row['pathname']
                    ,'resourceClass' => $row['resource_class']
                    ,'createdDate' => $row['created_dt']
                    ,'modifiedDate' => $row['modified_dt']
                    ,'redirectOnError' => boolval($row['redirect_on_error'])
                    ,'published' => boolval($row['published'])
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


        $resourceId = $this->validateIntParameter('id');
        $this->connectSql();
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
            if (!isset($resourceId)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
                $this->statusMessage = 'DELETE requests require the parameter "id" in the query string to specify a resource to be deleted.';
                break;
            }

            // if user is not an admin...
            if (!$this->sessionManager->userIsAdmin) {
                // Check whether user has write access to this page and ALL its children (since deletion is also recursive).
                $userHasAccess = $this->recursiveCheckWriteAccess($userId, $resourceId);
                if (!$userHasAccess) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                    $this->statusMessage = 'DELETE access denied; you do not have write access to this page or one of its children.';
                    $this->resourceData = array('status' => $this->statusMessage);
                    break;
                }
            }

            // Validation has passed; delete the page.
            // Note: sp_delete_resource is recursive; it also deletes any children the page has.
            $sql = "CALL sp_delete_resource(:resourceId)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array('resourceId' => $resourceId));
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');

        } while(false);

        return $this->resourceData;
    }
}
