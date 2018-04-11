<?php
/*
 * DataResource.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * iResource implementation representing nested data objects.
 *
 */

include_once LIBPATH.'php/toonces.php';

class DataResource implements iResource
{
    var $dataObjects = array();
    var $fields = array();
    var $resourceID;
    var $statusMessage = '';
    var $httpStatus;
    var $sessionManager;

    function authenticateUser() {
        // Toonces Core Services API uses Basic Auth for authentication, and the same
        // user structure as Toonces Admin.
        // Returns a user ID if login valid, null if not.
        $userID = NULL;
        
        // If there is no SessionManager object, instantiate one now.
        if (!$this->sessionManager)
            $this->sessionManager = new SessionManager($this->pageView->getSQLConn());
            
        if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER) ) {
            $email = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];
                
            $loginSuccess = $this->sessionManager->login($email, $pw);
            if ($loginSuccess)
                $userID = $this->sessionManager->userId;
        }
            
        return $userId;
    }

    function validateHeaders() {
        // Confirms that the HTTP request has the required headers.
        $headersValid = false;
        $headers = apache_request_headers();
        if (array_key_exists('content-type', $headers))
            if ($headers['content-type'] == 'application/json')
                $headersValid = true;
                
                return $headersValid;
    }


    public function validateIdParameter() {
        // By design, most endpoints will accept an "id" parameter in GET/PUT/DELETE requests.
        // There are 3 possible states:
        //      1. No ID parameter - return null
        //      2. ID parameter is set, but isn's an integer - Return 0
        //      3. ID parameter is set and is an integer - Return ID
        $id = null;
        do {
            if (!array_key_exists('id', $_GET)) // Parameter exists?
                break;
            
            if (!is_int($_GET['id'])) {          // it's an integer? 
                $id = $_GET['ID'];
            } else {
                $id = 0;
            }
                    
        } while (false);

        return $id;        
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
                $this->statusMessage = 'This object must be an array.';
                break;
            }
            
            // Iterate through each field and check for validity.
            foreach ($this->fields as $key => $field) {
                
                // Is the POST/PUT data missing any required fields?
                $fieldExists = array_key_exists($key, $data);
                if ($field->isRequired && !$fieldExists) {
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

            if (count($missingFields)) {
                // One or more required fields is missing - Break here.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'One or more required fields is missing: ' . implode(', ', $missingFields);
                break;
            }

            if (count($invalidFields)) {
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
    
    public function addElement ($element) {
        array_push($this->dataObjects,$element);
    }
    

    // execution method
    public function getResource() {
        // Validate headers. If valid, call the appropriate method depending on the request HTTP method.
        if ($this->validateHeaders()) {
        
            $method = $_SERVER['REQUEST_METHOD'];
            switch ($method) {
                case 'GET':
                    $this->getAction();
                    break;
                case 'POST':
                    $this->postAction();
                    break;
                case 'HEAD':
                    $this->headAction();
                    break;
                case 'PUT':
                    // $this->putAction(file_get_contents('php://input'));
                    $this->putAction();
                    break;
                case 'OPTIONS':
                    break;
                case 'DELETE':
                    $this->deleteAction();
                case 'CONNECT':
                    $this->connectAction();
                    break;
            }
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Missing required HTTP headers.';
        }

        return $this->dataObjects;
    }
    
    public function getAction() {
        // Override to define the resource's response to a GET request.
    }
    
    public function postAction() {
        // foo
    }

    public function headAction() {
        // foo
    }
    
    public function putAction() {
        // foo
    }
    
    public function deleteAction() {
        // foo
    }
    
    public function connectAction() {
        // foo
    }

}
