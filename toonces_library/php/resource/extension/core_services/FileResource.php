<?php
/**
 * @author paulanderson
 * FileResource.php
 * 
 * A Resource subclass providing and regulating access to supporting files. 
 *
 * 
 * */

include_once LIBPATH.'php/toonces.php';

class FileResource extends Resource implements iResource {
    
    var $resourcePath;
    var $requestPath;
    var $httpStatus;
    var $outputData;
    
    public function validateHeaders() {
        // whatevs
        return true;
    }
    
    public function getAction() {
        // Acquire the file name
        if (!isset($this->requestPath))
            $this->requestPath = $_SERVER['REQUEST_URI'];
        
        $filename = preg_replace('^.+/', '', $this->requestPath);
        $fileVector = $this->resourcePath . $filename;
        
        
        do {
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
            $this->outputData = $fileVector;
        } while (false);
        
        return $this->outputData;
        
    }
    
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
                    
                    // Act depending on the HTTP verb.
                    // Note: Not using a switch statement here to preserve object state.
                    if ($this->httpMethod == 'GET')
                        $this->getAction();
                    /*
                        elseif ($this->httpMethod == 'POST')
                        //$this->postAction();
                        elseif ($this->httpMethod == 'HEAD')
                        //$this->headAction();
                        elseif ($this->httpMethod == 'PUT')
                        //$this->putAction();
                        elseif ($this->httpMethod == 'OPTIONS')
                        //$this->optionsAction();
                        elseif ($this->httpMethod == 'DELETE')
                        //$this->deleteAction();
                        elseif ($this->httpMethod == 'CONNECT')
                        //$this->connectAction();
                         * 
                         */
                        else
                            throw new Exception('Error: DataResource object getResource() was called without a valid HTTP verb ($httpMethod). Supported methods are GET, POST, HEAD, PUT, OPTIONS, DELETE, CONNECT.');
                            
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
            $this->statusMessage = 'Missing required HTTP headers.';
        }
        
        return $this->outputData;
    }
}