<?php
/**
 * @author paulanderson
 * FilePageView.php
 * Initial commit: Paul Anderson, 4/26/2018
 * 
 * PageView extension for handling transfers of files from a vector.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class FilePageview extends ApiPageView implements iPageView, iResource {

    public function renderPage() {
        // Called by index.php - Serves a file download, if applicable.
        
        // $dataObject will be a FileResource or similar Resource subclass instance.
        $dataObject = $this->getResource();

        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $dataObject->httpStatus;
        $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');
        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');
        
        // Execute the object. 
        $resourceUri = $dataObject->getResource();
        
        // If applicable - Say, this is a GET request - Start the transfer.
        if ($resourceUri) {
            // Extract the file name 
            $filename = preg_replace('^.+/', '', $resourceUri);
            $headerStr = 'Content-Disposition: attachment; filename="' . $filename . '"';
            header($headerStr);
            // Stop output buffering
            if (ob_get_level()) {
                ob_end_flush();
            }
            
            flush();
            readfile($resourceUri);
        }

        
    }
}