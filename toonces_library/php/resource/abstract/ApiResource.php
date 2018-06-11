<?php
/**
 * @author paulanderson
 * ApiResource.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Abstract class providing common functionality for API resource classes.
 *
 */

include_once LIBPATH.'php/toonces.php';

abstract class ApiResource extends Resource implements iResource {

    var $resourceData;
    var $httpStatus;
    var $httpMethod;
    var $resourceUrl;
    var $resourceUri;
    var $sessionManager;

    function authenticateUser() {
        // Toonces Core Services API uses Basic Auth for authentication, and the same
        // user structure as Toonces Admin.
        // Returns a user ID if login valid, null if not.
        $userId  = NULL;

        // If there is no SessionManager object, instantiate one now.
        if (!$this->sessionManager)
            $this->sessionManager = new SessionManager($this->pageViewReference->getSQLConn());

        if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER) ) {
            $email = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];
            $loginSuccess = $this->sessionManager->login($email, $pw, $this->pageViewReference->pageId);
            if ($loginSuccess)
                $userId = $this->sessionManager->userId;
        }

        return $userId;
    }


    // execution method
    /**
     * @return mixed
     * @throws Exception
     */
    public function getResource() {

        // Get the resource URI if it hasn't already been set externally
        if (!$this->resourceUri)
            $this->resourceUri = GrabPageURL::getURL($this->pageViewReference->pageId, $this->pageViewReference->getSQLConn());

        // Build the full URL path
        $scheme = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) ? 'https://' : 'http://';
        $this->resourceUrl = $scheme . $_SERVER['HTTP_HOST'] . '/' . $this->resourceUri;

        // Acquire the HTTP verb from the server if not set externally.
        if (!$this->httpMethod)
            $this->httpMethod = $_SERVER['REQUEST_METHOD'];

        // Act depending on the HTTP verb.
        // Note: Not using a switch statement here to preserve object state.
        if ($this->httpMethod == 'GET')
            $this->getAction();
            elseif ($this->httpMethod == 'POST')
                $this->postAction();
            elseif ($this->httpMethod == 'HEAD')
                $this->headAction();
            elseif ($this->httpMethod == 'PUT')
                $this->putAction();
            elseif ($this->httpMethod == 'OPTIONS')
                $this->optionsAction();
            elseif ($this->httpMethod == 'DELETE')
                $this->deleteAction();
            elseif ($this->httpMethod == 'CONNECT')
                $this->connectAction();
            else
                throw new Exception('Error: DataResource object getResource() was called without a valid HTTP verb ($httpMethod). Supported methods are GET, POST, HEAD, PUT, OPTIONS, DELETE, CONNECT.');

        return $this->resourceData;
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
