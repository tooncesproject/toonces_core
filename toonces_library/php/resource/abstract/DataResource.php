<?php
/*
 * DataResource.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * Abstract class providing common functionality for REST API resources.
 *
 */

include_once LIBPATH.'php/toonces.php';

abstract class DataResource extends ApiResource implements iResource
{
    var $resourceData = array();
    var $fields = array();
    var $statusMessage = '';

    /**
     * @var iApiDataValidator
     */
    var $apiDataValidator;


    /**
     * @return bool
     */
    function validateHeaders() {
        // Confirms that the HTTP request has the required headers.
        $headersValid = false;

        if (array_key_exists('CONTENT_TYPE', $_SERVER))
            if ($_SERVER['CONTENT_TYPE'] == 'application/json')
                $headersValid = true;

            return $headersValid;
    }


    /**
     * @param $parameterKey
     * @return int|null
     */
    public function validateIntParameter($parameterKey) {
        // This method provides basic validation for any named GET parameters expecting an integer.
        // It will return an integer.
        // There are 3 possible states:
        //      1. Key not found in parameters - return null
        //      2. Key is set, but value isn's an integer - Return 0
        //      3. Key is set and value is an integer - Return value.
        $id = null;
        $getParams = $this->parameters;
        do {
            if (!array_key_exists($parameterKey, $getParams)) // Parameter exists?
                break;

            if (is_int(intval($getParams[$parameterKey]))) {          // it's an integer?
                $id = intval($getParams[$parameterKey]);
            } else {
                $id = 0;
            }

        } while (false);

        return $id;
    }


    /**
     * @return bool
     */
    function getSubResources() {
        // Acquires any endpoints that are children of the current endpoint and provides the URLs of those endpoints.
        // Return:
        //      true if resources are available. Resources added to DataObjects and $httpStatus set to 200.
        //      false if no resources are available. $httpStatus set to 418 (haha)

        $sqlConn = $this->pageViewReference->getSQLConn();

        // Acquire the user id if this is an authenticated request.
        $userId = $this->authenticateUser() ?? 0;

        // Query the database for any children of the current resource.
        $sql = <<<SQL
            SELECT
                 r.resource_id
                ,r.pathname
                ,r.page_title
            FROM resource_hierarchy_bridge rhb
            JOIN resource r ON rhb.descendant_resource_id = r.resource_id
            LEFT JOIN resource_user_access rua ON r.resource_id = rua.resource_id AND (rua.user_id = :userId)
            LEFT JOIN users u ON u.user_id = :userId
            WHERE
                (rhb.resource_id = :resourceId)
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
        $stmt = $sqlConn->prepare($sql);
        $sqlParams = array('userId' => $userId, 'resourceId' => $this->pageViewReference->resourceId);
        $stmt->execute($sqlParams);
        $result = $stmt->fetchAll();
        if ($result) {
            // Results? Serialize the output.
            $subResources = array();
            foreach ($result as $row) {
                $subResources[$row[0]] = array(
                     'url' => $this->resourceUrl . $row[1]
                    ,'title' => $row[2]
                );
            }
            $this->resourceData['resources'] = $subResources;
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            return true;
        } else {
            // No joy? Return a general error.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_418_IM_A_TEAPOT', 'EnumHTTPResponse');
            return false;
        }


    }


    /**
     * @param $data
     * @return bool
     */
    public function validateData($data) {
        // Iterate through keys in dataObjects array

        $postValid = false;

        // Check validation.
        do {
            // We go through each requirement in order of priority
            // The first requirement for DataResource is that the data is an array
            // (JSON should already be validated and converted to array at this point).

            if (!$this->apiDataValidator->validateDataStructure($data)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'The API only accepts well-formed JSON.  ';
                break;
            }

            $missingFields = $this->apiDataValidator->getMissingRequiredFields($data);
            $invalidFields = $this->apiDataValidator->getInvalidFields($data);

            if (!empty($missingFields)) {
                // One or more required fields is missing - Break here.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'One or more required fields is missing: ' . implode(', ', $missingFields);
                break;
            }

            if (!empty($invalidFields)) {
                 // One or more fields had bogus data.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $errorArray = array();
                foreach($invalidFields as $invalidKey => $value) {
                    array_push($errorArray, $invalidKey . ': ' . $value);
                }
                $this->statusMessage = implode(', ', $errorArray);
                break;
            }
            // If we've made it this far, we're OK.
            $postValid = true;


        } while (false);
        return $postValid;

    }


    /**
     * @return array|null
     * @throws Exception
     */
    public function getResource() {
        // Override APIResource::getResource
        $returnData = null;
        if ($this->validateHeaders()) {
            // Headers valid; call superclass
            $returnData = parent::getResource();
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Missing required HTTP headers.';
        }

        return $returnData;
    }


    /**
     * @throws Exception
     */
    public function render() {
        $renderer = new JsonRenderer();
        $renderer->renderResource($this);

    }

}
