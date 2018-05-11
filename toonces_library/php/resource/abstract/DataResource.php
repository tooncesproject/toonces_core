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


    function validateHeaders() {
        // Confirms that the HTTP request has the required headers.
        $headersValid = false;

        if (array_key_exists('CONTENT_TYPE', $_SERVER))
            if ($_SERVER['CONTENT_TYPE'] == 'application/json')
                $headersValid = true;

            return $headersValid;
    }


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

    function getSubResources() {
        // Acquires any endpoints that are children of the current endpoint and provides the URLs of those endpoints.
        // Return:
        //      true if resources are available. Resources added to DataObjects and $httpStatus set to 200.
        //      false if no resources are available. $httpStatus set to 418 (haha)

        $sqlConn = $this->pageViewReference->getSQLConn();

        // Acquire the user id if this is an authenticated request.
        $userID = $this->authenticateUser() ?? 0;

        // Query the database for any children of the current page.
        $sql = <<<SQL
            SELECT
                 p.page_id
                ,p.pathname
                ,p.page_title
            FROM page_hierarchy_bridge phb
            JOIN pages p ON phb.descendant_page_id = p.page_id
            LEFT JOIN page_user_access pua ON p.page_id = pua.page_id AND (pua.user_id = :userID)
            LEFT JOIN users u ON u.user_id = :userID
            WHERE
                (phb.page_id = :pageID)
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
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('userID' => $userID, 'pageID' => $this->pageViewReference->pageId));
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

    public function validateData($data) {
        // Iterate through keys in dataObjects array

        $postValid = false;
        $missingFields = array();
        $invalidFields = array();

        // Check validation.
        do {
            // We go through each requirement in order of priority
            // The first requirement for DataResource is that the data is an array
            // (JSON should already be validated and converted to array at this point).

            if (!is_array($data)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'The API only accepts well-formed JSON.  ';
                break;
            }

            // Iterate through each field and check for validity.
            foreach ($this->fields as $key => $field) {

                // Is the POST/PUT data missing any required fields?
                $fieldExists = array_key_exists($key, $data);
                if (!$field->allowNull && !$fieldExists) {
                    // Required key missing
                    array_push($missingFields, $key);
                }

                // Is the input data valid, per the data object's requirements?
                if ($fieldExists) {
                    if (!$field->validateData($data[$key])) {
                        // Field does not pass validation
                        $invalidFields[$key] = $field->statusMessage;
                    }
                }
            }

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
}
