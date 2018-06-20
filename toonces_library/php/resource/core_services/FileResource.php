<?php
/**
 * @author paulanderson
 * FileResource.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * A Resource subclass providing and regulating access to supporting files.
 *
 *
 * */

include_once LIBPATH . 'php/toonces.php';

class FileResource extends Resource implements iFileResource {

    var $resourcePath;
    var $requestPath;
    var $requireAuthentication;
    var $resourceId;

    public function validateGetHeaders() {
        // Override if the implementation requires any specific HTTP headers for GET requests.
        return true;
    }


    public function validatePutHeaders() {
        // Override if the implementation requires any specific HTTP headers for PUT requests.
        return true;
    }


    public function validateDeleteHeaders() {
        // Override if the implementation requires any specific HTTP headers for DELETE requests.
        return true;
    }


    public function putAction() {
        // Acquire the file name
        if (!isset($this->requestPath))
            $this->requestPath = $_SERVER['REQUEST_URI'];

        // Sanitize path
        $this->requestPath = parse_url($this->requestPath, PHP_URL_PATH);

        // Acquire SQL conn if not set
        if (!isset($this->conn))
            $this->conn = UniversalConnect::doConnect();


        $filename = preg_replace('~^.+/~', '', $this->requestPath);
        $fileVector = $this->resourcePath . $filename;

        // Acquire the PUT body (if not already set)
        if (!isset($this->resourceData))
            $this->resourceData = file_get_contents("php://input");

        do {
            // Go through the validation sequcence.
            // Authenticate user.
            $userId = $this->authenticateUser();
            if ($userId == 0) {
                // Security through obscurity; 404 status if authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $this->resourceId, $this->conn);
            if (!$userHasAccess) {
                // Security through obscurity; 404 status if user not authorized.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Validate headers.
            if (!$this->validatePutHeaders()) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                break;
            }

            // Filename length
            if (strlen($filename) == 0 or $filename =='/') {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                break;
            }

            // Data length.
            if (empty($this->resourceData)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                break;
            }

            // default successful HTTP status is 201 (created), but it will respond with 200 if the file
            // already exists.
            $successHttpStatus = Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse');
            if (file_exists($fileVector))
                $successHttpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');

            // Attempt to copy the file.
            $bytesWritten = null;
            try {
                $bytesWritten = file_put_contents($fileVector, $this->resourceData);
                $this->httpStatus = $successHttpStatus;
            } catch (Exception $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                break;
            }

            if ($bytesWritten === false) {
                // If file write operation failed silently, return an ISE.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
            }

        } while (false);


    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAction() {
        // Acquire the file name
        if (!isset($this->requestPath))
            $this->requestPath = $_SERVER['REQUEST_URI'];

        $filename = preg_replace('~^.+/~', '', $this->requestPath);
        $fileVector = $this->resourcePath . $filename;

        // Acquire SQL connection if not set
        if (!isset($this->conn))
            $this->conn = UniversalConnect::doConnect();

        do {
            // Authenticate, if required.
            if ($this->requireAuthentication) {
                $userId = $this->authenticateUser();
                if ($userId == 0) {
                    // Security through obscurity; 404 status if authentication failed.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                    break;
                }

                $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $this->resourceId, $this->conn);
                if (!$userHasAccess) {
                    // Security through obscurity; 404 status if authentication failed.
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                    break;
                }
            }

            // Validate headers.
            if (!$this->validateGetHeaders()) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                break;
            }

            // Ensure the resourcePath variable is set
            if (!isset($this->resourcePath)) {
                throw new Exception('Error: FileResource->getAction() was called without the FileResource object\'s resourcePath variable being set first.');
            }
            //  Check that file exists. If not, send a 404 error.
            if(!file_exists($fileVector)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Okie dokie? Add the vector to the output data.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            $this->resourceData = $fileVector;
        } while (false);

        return $this->resourceData;

    }


    public function deleteAction() {
        // Deletes the file resource.
        if (!isset($this->requestPath))
            $this->requestPath = $_SERVER['REQUEST_URI'];

        $filename = preg_replace('~^.+/~', '', $this->requestPath);
        $fileVector = $this->resourcePath . $filename;

        // Acquire SQL connection if not set.
        if (!isset($this->conn))
            $this->conn = UniversalConnect::doConnect();

        do {
            // Go through the validation sequcence.
            // Authenticate user.
            $userId = $this->authenticateUser();
            if ($userId == 0) {
                // Security through obscurity; 404 status if authentication failed.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            $userHasAccess = CheckResourceUserAccess::checkUserAccess($userId, $this->resourceId, $this->conn);
            if (!$userHasAccess) {
                // Security through obscurity; 404 status if user not authorized.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Validate headers.
            if (!$this->validateDeleteHeaders()) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                break;
            }

            // Check file existence
            if (!file_exists($fileVector)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
                break;
            }

            // Attempt delete operation.
            $deleteSuccess = null;
            try {
                $deleteSuccess = unlink($fileVector);
            } catch (Exception $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                break;
            }

            if ($deleteSuccess) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');
            } else {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
            }

        } while (false);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getResource() {
        // Add functionality to ApiResource::getResource - Validate the resourcePath
        if (!isset($this->resourcePath))
            throw new Exception('Error: getResource() was called without the resourcePath variable set.');

        if (!file_exists($this->resourcePath))
            throw new Exception('Error: getResource() was called without a valid resourcePath variable set.');

        return parent::getResource();
    }


    /**
     * @throws Exception
     */
    public function render() {
        $renderer = new FileRenderer();
        $renderer->renderResource($this);
    }

}
