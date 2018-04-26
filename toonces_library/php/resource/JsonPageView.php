<?php
/*
 * JsonPageView.php
 * Initial commit: Paul Anderson, 1/24/2018
 * 
 * iPageView implementation for REST API resources.
 * Analagous to the web view renderer element HTMLPageView.
 * Converts to JSON and renders data from DataResource objects. 
 * 
 */

require_once LIBPATH.'php/toonces.php';

class JsonPageView extends ApiPageView implements iPageView, iResource
{
   
    public function renderPage() {
        // Called by index.php - Converts the resource data to JSON, executes the server's response. 
        $dataObject = $this->getResource();

        // Execute the object
        $resourceData = $dataObject->getResource();
        // If the resource has a status message, add it to the output
        if ($dataObject->statusMessage)
            $resourceData['status'] = $dataObject->statusMessage;

        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $dataObject->httpStatus;
        $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');
        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');
        
        // Encode as JSON and render.
        $JSONString = json_encode($resourceData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        header($httpStatusString, true, $httpStatus);
        echo($JSONString);
        
    }
}