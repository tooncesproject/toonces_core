<?php
/**
 * @author paulanderson
 * ExtHtmlPageDataResource.php.php
 * Initial commit: 5/5/2018
 *
 * DataResource class (and subclass of PageDataResource) for managing HTML-content pages.
 *
 */

require_once LIBPATH . 'php/toonces.php';

class ExtHtmlPageDataResource extends PageDataResource implements iResource {

    /**
     * @var iResourceClient
     */
    var $client;

    /**
     * @var string
     */
    var $urlPath;

    /**
     * @param int $resourceId
     * @return int
     */
    function setupClient($resourceId = null) {
        // woo!
        $conn = $this->pageViewReference->getSQLConn();
        $clientClass = null;
        if (isset($this->resourceData['clientClass']))
        $clientClass = $this->resourceData['clientClass'];

        // Only instantiate the client if it hasn't been set externally
        // (Unit tests will set a "dummy" client)
        if (!isset($this->client)) {
            // Class set in parameters?
            if (!$clientClass && $resourceId) {
                // If not set in parameters, query the database for the client class
                $sql = "SELECT client_class FROM ext_html_page WHERE resource_id = :resourceId";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['resourceId' => $resourceId]);
                $result = $stmt->fetchAll();
                $clientClass = $result[0]['client_class'];
            }

            // Attempt to instantiate the client
            try {
                $this->client = new $clientClass($this->pageViewReference);
            } catch (Exception $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Failed to instantiate the ResourceClient object: '. $e->getMessage();
                return 1;
            }
        }
        return 0;
    }


    /**
     * Instantiate an APIDataValidator outside PostAction so it isn't inherited.
     */
    function instantiatePostValidator() {
        $this->apiDataValidator = new ExtHtmlPagePostApiDataValidator();
    }


    /**
     * Instantiate an APIDataValidator outside PutAction so it isn't inherited.
     */
    function instantiatePutValidator() {
        $this->apiDataValidator = new PagePutApiDataValidator();
    }


    /**
     * override PageDataResource->postAction
     * @return array
     */
    function postAction() {

        $conn = $this->pageViewReference->getSQLConn();

        $this->instantiatePostValidator();

        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        // Set up default values.
        if (!isset($this->resourceData['pageBuilderClass']))
            $this->resourceData['pageBuilderClass'] = 'ExtHTMLPageBuilder';

        if (!isset($this->resourceData['pageViewClass']))
            $this->resourceData['pageViewClass'] = 'HTMLPageView';

        if (!isset($this->resourceData['clientClass'])) {
            // If not already set, get the default client class from toonces-config.xml
            $xml = new DOMDocument();
            $xml->load(ROOTPATH.'toonces-config.xml');
            $pathNode = $xml->getElementsByTagName('default_resource_client')->item(0);
            $this->resourceData['clientClass']  = $pathNode->nodeValue;
        }

        $dataValid = $this->validateData($this->resourceData);
        // Go through validation and POST actions.
        do {
            // Attempt to instantiate the client
            $clientStatus = $this->setupClient(null);
            // Break if error.

            if ($clientStatus == 1)
                break;

            // Validate the input. If invalid, authenticate the user;
            // We don't want to show our private parts to an unauthenticated
            // someone or other.
            // If user is validated, simply break and return the invalidation
            // message.

            if ($dataValid == false) {
                $userId = $this->authenticateUser();
                if (empty($userId)) {
                    // Authentication failed.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                    $this->statusMessage = 'Access denied. Go away.';
                    $this->resourceData = array('status' => $this->statusMessage);
                }
                break;
            }

            // Acquire critical variables body prior to resource creation
            $htmlBody = $this->resourceData['htmlBody'];
            $clientClass = $this->resourceData['clientClass'];

            // Attempt to create the resource
            parent::postAction();

            if ($this->httpStatus == Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse')) {
                $postResult = parent::getAction();
            }

            // If successful, load the HTML to the store and create a record in
            // ext_html_pages
            // If there was an error in calling the parent methods, break.
            if ($this->httpStatus != Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'))
                break;

            $resourceId = key($postResult);
            $date = $postResult[$resourceId]['createdDate'];
            $fileNameDate = preg_replace('[ ]', '_', $date);
            $fileNameDate = preg_replace('[:]','',$fileNameDate);

            // Get the resource URL from toonces_config.xml,
            // if not already set.
            if (!isset($this->urlPath)) {
                $xml = new DOMDocument();
                $xml->load(ROOTPATH.'toonces-config.xml');
                $pathNode = $xml->getElementsByTagName('html_resource_url')->item(0);
                $this->urlPath = $pathNode->nodeValue;
            }

            // Generate a file URL
            $fileUrl = $this->urlPath . strval($resourceId) . '_' . $fileNameDate . '.htm';

            // Create the file
            $email = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];
            $clientResponse = $this->client->put($fileUrl, $htmlBody, $email, $pw);
                $clientStatus = $this->client->getHttpStatus();

            // If file creation was unsuccessful, roll back, break and error.
            if ($clientStatus != 200 && $clientStatus != 201) {
                $this->parameters['id'] = strval($resourceId);
                parent::deleteAction();
                $this->httpStatus = $clientStatus;
                $this->resourceData = $clientResponse;
                break;
            }

            // Insert a record into ext_html_page
            $sql = <<<SQL
                INSERT INTO ext_html_page
                    (resource_id, html_path, client_class)
                VALUES
                    (:resourceId, :htmlPath, :clientClass)
SQL;
            $stmt = $conn->prepare($sql);

            $sqlParams = array(
                 'resourceId' => $resourceId
                ,'htmlPath' => $fileUrl
                ,'clientClass' => $clientClass
            );
            try {
                $stmt->execute($sqlParams);
            } catch (PDOException $e) {
                // If unsuccessful, delete the resource record.
                $this->parameters['id'] = strval($resourceId);
                parent::deleteAction();
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'PDO error occured when inserting into ext_html_page: ' . $e->getMessage();
                break;
            }

            // Success?
            $this->parameters['id'] = strval($resourceId);
            $this->getAction();
            $this->httpStatus = Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse');

            // Append the file URL to the output
            $this->resourceData['fileUrl'] = $fileUrl;

        } while (false);

        return $this->resourceData;
    }

    /**
     * @return array
     */
    public function putAction() {
        $conn = $this->pageViewReference->getSQLConn();
        // Acquire the PUT body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);

        $this->instantiatePutValidator();

        $clientClass = null;
        $htmlBody = null;
        // Get the body if applicable.
        if (isset($this->resourceData['htmlBody'])) {
            $htmlBody = $this->resourceData['htmlBody'];
        }
        if (isset($this->resourceData['clientClass']))
            $clientClass = $this->resourceData['clientClass'];

        $dataValid = $this->validateData($this->resourceData);

        do {

            // Validate the input. If invalid, authenticate the user;
            // We don't want to show our private parts to an unauthenticated
            // someone or other.
            // If user is validated, simply break and return the invalidation
            // message.

            if ($dataValid == false) {
                $userId = $this->authenticateUser();
                if (empty($userId)) {
                    // Authentication failed.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                    $this->statusMessage = 'Access denied. Go away.';
                    $this->resourceData = array('status' => $this->statusMessage);
                }
                break;
            }

            // Call parent
            parent::putAction();
            // Page record updated successfully?
            if ($this->httpStatus != Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse')) {
                break;
            } else {
                parent::getAction();
            }

            // If htmlBody was set, upload the document and update ext_html_page
            $resourceId = $this->parameters['id'];

            $fileUrl = null;
            if ($htmlBody) {
                // Attempt to instantiate the client
                $clientStatus = $this->setupClient($resourceId);
                // Break if error.
                if ($clientStatus == 1)
                    break;

                // Get current datetime from SQL server as basis for file name.
                $sql = "SELECT CURRENT_TIMESTAMP()";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $date = $result[0][0];

                $fileNameDate = preg_replace('[ ]', '_', $date);
                $fileNameDate = preg_replace('[:]','',$fileNameDate);

                // Get the resource URL from toonces_config.xml
                if (!isset($this->urlPath)) {
                    $xml = new DOMDocument();
                    $xml->load(ROOTPATH.'toonces-config.xml');
                    $pathNode = $xml->getElementsByTagName('html_resource_url')->item(0);
                    $this->urlPath = $pathNode->nodeValue;
                }

                // Generate a file URL
                $fileUrl = $this->urlPath . strval($resourceId) . '_' . $fileNameDate . '.htm';

                // Create the file
                $email = $_SERVER['PHP_AUTH_USER'];
                $pw = $_SERVER['PHP_AUTH_PW'];
                $this->client->put($fileUrl, $htmlBody, $email, $pw);
                $clientStatus = $this->client->getHttpStatus();
                if ($clientStatus != 200 && $clientStatus != 201) {
                    $this->httpStatus = $clientStatus;
                    $this->resourceData['status'] = 'Partial success; failed to upload body file.';
                    break;
                }

                // Update the record if success
                $sql = <<<SQL
                INSERT INTO ext_html_page
                    (resource_id, html_path, client_class)
                    SELECT
                         :resourceId
                        ,:htmlPath
                        ,COALESCE(:clientClass, client_class)
                    FROM
                        dom_resource
                    WHERE
                        resource_id = :resourceId
                ON DUPLICATE KEY UPDATE
                     resource_id = VALUES(resource_id)
                    ,html_path = VALUES(html_path)
                    ,client_class = VALUES(client_class)
SQL;
                $stmt = $conn->prepare($sql);
                $sqlParams = array(
                    'resourceId' => $resourceId
                    ,'htmlPath' => $fileUrl
                    ,'clientClass' => $clientClass
                );
                try {
                    $stmt->execute($sqlParams);
                } catch (PDOException $e) {
                    // If unsuccessful, delete the resource record.
                    $this->parameters['id'] = strval($resourceId);
                    parent::deleteAction();
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                    $this->statusMessage = 'PDO error occured when inserting into ext_html_page: ' . $e->getMessage();
                    break;
                }
            }

            // Success
            $this->getAction();
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');

            // Append the file URL to the output
            $this->resourceData['fileUrl'] = $fileUrl;

        } while (false);

        return $this->resourceData;

    }

    /**
     * @return array
     */
    public function getAction() {
        // Query the database for the resource, depending upon parameters
        // First - Validate GET parameters
        $resourceId = $this->validateIntParameter('id');
        $conn = $this->pageViewReference->getSQLConn();

        do {
            // GET requests require authentication at this endpoint.
            $userId = $this->authenticateUser();
            if (empty($userId)) {
                $this->statusMessage = 'Access denied. Go away.';
                $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
                break;
            }

            // OK so far? Build the query.
            $sql = <<<SQL
                SELECT
                     p.resource_id
                    ,rhb.resource_id AS ancestor_resource_id
                    ,pathname
                    ,page_title
                    ,pagebuilder_class
                    ,pageview_class
                    ,p.created_dt
                    ,p.modified_dt
                    ,redirect_on_error
                    ,published
                    ,ehp.html_path
                    ,ehp.client_class
                FROM resource p
                JOIN dom_resource ehp ON p.resource_id = ehp.resource_id
                -- join to PHB is to get the parent resource ID
                LEFT JOIN resource_hierarchy_bridge rhb ON p.resource_id = rhb.descendant_resource_id
                LEFT JOIN resource_user_access rua ON p.resource_id = rua.resource_id AND (rua.user_id = :userId)
                LEFT JOIN users u ON u.user_id = :userId
                WHERE
                    (p.resource_id = :resourceId OR :resourceId IS NULL)
                    AND
                    (
                        (p.published = 1 AND p.deleted IS NULL)
                        OR
                        rua.user_id IS NOT NULL
                        OR
                        u.is_admin = TRUE
                    )
                ORDER BY p.resource_id ASC
SQL;
            // if the id parameter is 0, it's bogus. Only query if it's null or >= 1.
            $result = null;
            if ($resourceId !== 0) {
                $stmt = $conn->prepare($sql);
                $sqlParams = array('userId' => $userId, 'resourceId' => $resourceId);
                $stmt->execute($sqlParams);
                $result = $stmt->fetchAll();
            }

            if ($result) {
                // Process the response
                foreach ($result as $row) {
                    $this->resourceData[$row[0]] = array(
                        'url' => $this->resourceUrl . '?id=' . strval($row['resource_id'])
                        ,'pageUri' => GrabResourceURL::getURL($row['resource_id'], $conn)
                        ,'ancestorResourceId' => intval($row['ancestor_resource_id'])
                        ,'pathName' => $row['pathname']
                        ,'pageTitle' => $row['page_title']
                        ,'pageBuilderClass' => $row['pagebuilder_class']
                        ,'pageViewClass' => $row['pageview_class']
                        ,'createdDate' => $row['created_dt']
                        ,'modifiedDate' => $row['modified_dt']
                        ,'redirectOnError' => boolval($row['redirect_on_error'])
                        ,'published' => boolval($row['published'])
                        ,'fileUrl' => $row['html_path']
                        ,'clientClass' => $row['client_class']
                    );
                }
                $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            } else {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
            }
        } while (false);
        return $this->resourceData;
    }

    /**
     * @return array
     */
    public function deleteAction() {

        $conn = $this->pageViewReference->getSQLConn();

        // Query the database for the file vector.
        $id = $this->validateIntParameter('id');
        $sql = <<<SQL
                SELECT html_path
                FROM dom_resource
                WHERE resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $id]);
        $result = $stmt->fetchAll();
        $htmlPath = $result[0]['html_path'];
        do {
            // No record in ext_html_page? Return 404.
            if (empty($htmlPath)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Set up client.
            $clientState = $this->setupClient(intval($id));
            // Client setup successfully?
            if ($clientState == 1)
                break;

            // Call parent - This will requre authentication.
            parent::deleteAction();

            // If delete of resource was successful, delete the file.
            if ($this->httpStatus == Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse')) {

                $email = $_SERVER['PHP_AUTH_USER'];
                $pw = $_SERVER['PHP_AUTH_PW'];
                $this->client->delete($htmlPath, $email, $pw);
                $clientStatus = $this->client->getHttpStatus();
                if ($clientStatus != Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse')) {
                    $this->resourceData['status'] = 'Failed to delete file ' . $htmlPath;
                }
                $this->httpStatus = $clientStatus;
            }

        } while (false);

        return $this->resourceData;
    }
}
