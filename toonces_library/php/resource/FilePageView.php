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

        // Execute the object.
        $resourcePath = $dataObject->getResource();

        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $dataObject->httpStatus;
        $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');
        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');

        // If applicable - Say, this is a GET request - Start the transfer.
        if ($resourcePath) {
            header($httpStatusString, true, $httpStatus);
            $headerStr = "Content-Type: application/octet-stream";
            header($headerStr);
            // Stop output buffering
            if (ob_get_level()) {
                ob_end_flush();
            }

            flush();
            readfile($resourcePath);
        } else {
            header($httpStatusString, true, $httpStatus);

        }
        // For testing purposes, we return the resource path supplied by the resource.
        return $resourcePath;

    }
}
