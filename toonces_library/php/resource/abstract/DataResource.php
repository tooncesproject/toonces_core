<?php
/*
 * DataResource.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * Abstract class providing common functionality for REST API resources.
 *
 */

include_once LIBPATH.'php/toonces.php';

abstract class DataResource extends Resource implements iResource
{
    var $dataObjects = array();
    var $fields = array();
    var $resourceID;
    var $statusMessage = '';
    var $httpStatus;
    var $httpMethod;
    var $sessionManager;
    var $resourceURL;
    var $resourceURI;

    function authenticateUser() {
        // Toonces Core Services API uses Basic Auth for authentication, and the same
        // user structure as Toonces Admin.
        // Returns a user ID if login valid, null if not.
        $userID = NULL;
        
        // If there is no SessionManager object, instantiate one now.
        if (!$this->sessionManager)
            $this->sessionManager = new SessionManager($this->pageViewReference->getSQLConn());
            
        if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER) ) {
            $email = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];
                
            $loginSuccess = $this->sessionManager->login($email, $pw);
            if ($loginSuccess)
                $userID = $this->sessionManager->userId;
        }
            
        return $userID;
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


    public function validateIntParameter($parameterKey) {
        // This method provides basic validation for any named GET parameters expecting an integer.
        // There are 3 possible states:
        //      1. Key not found in parameters - return null
        //      2. Key is set, but value isn's an integer - Return 0
        //      3. Key is set and value is an integer - Return value.
        $id = null;
        $getParams = $this->parameters;
        do {
            if (!array_key_exists($parameterKey, $getParams)) // Parameter exists?
                break;
            
            if (!is_int($getParams[$parameterKey])) {          // it's an integer? 
                $id = $getParams[$parameterKey];
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
            
            // Get the resource URI if it hasn't already been set externally
            if (!$this->resourceURI)
                $this->resourceURI = $this->pageViewReference->getPageURI();

            // Build the full URL path
            $scheme = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) ? 'https://' : 'http://';
            $this->resourceURL = $scheme . $_SERVER['HTTP_HOST'] . '/' . $this->resourceURI;
            
            // Acquire the HTTP verb from the server if not set externally.
            if (!$this->httpMethod)
                $this->httpMethod = $_SERVER['REQUEST_METHOD'];
        
            switch ($this->httpMethod) {
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
                    $this->putAction();
                    break;
                case 'OPTIONS':
                    break;
                case 'DELETE':
                    $this->deleteAction();
                case 'CONNECT':
                    $this->connectAction();
                    break;
                default:
                    // If not supported, throw an exception.
                    throw new Exception('Error: DataResource object getResource() was called without a valid HTTP verb ($httpMethod). Supported methods are GET, POST, HEAD, PUT, OPTIONS, DELETE, CONNECT.');
            }
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Missing required HTTP headers.';
        }

        return $this->dataObjects;
    }
    
    public function getAction() {
        // Override to define the resource's response to a GET request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
    
    public function postAction() {
        // Override to define the resource's response to a POST request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }

    public function headAction() {
        // Override to define the resource's response to a HEAD request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
    
    public function putAction() {
        // Override to define the resource's response to a PUT request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
    
    public function deleteAction() {
        // Override to define the resource's response to a DELETE request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
    
    public function connectAction() {
        // Override to define the resource's response to a CONNECT request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }

    public function optionsAction() {
        // Override to define the resource's response to a OPTIONS request.
        // Default behavior is a 'method not allowed' error (if it isn't implemented).
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
    
}