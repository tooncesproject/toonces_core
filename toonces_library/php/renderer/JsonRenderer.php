<?php
/*
 * JsonRenderer.php
 * Initial commit: Paul Anderson, 1/24/2018
 *
 * iPageView implementation for REST API resources.
 * Analagous to the web view renderer element HTMLPageView.
 * Converts to JSON and renders data from DataResource objects.
 *
 */

require_once LIBPATH . 'php/toonces.php';

class JsonRenderer extends Renderer implements iRenderer
{

    /**
     * @param DataResource $paramResource
     * @throws Exception
     */
    public function renderResource($paramResource) {

        // Execute the object
        $resourceData = $paramResource->getResource();
        // If the resource has a status message, add it to the output
        if ($paramResource->statusMessage)
            $resourceData['status'] = $paramResource->statusMessage;

        // Once executed, the resource must have an HTTP status.
        // If it doesn't, throw an exception.
        $httpStatus = $paramResource->httpStatus;
        $httpStatusString = null;
        if (isset($httpStatus))
            $httpStatusString = Enumeration::getString($httpStatus, 'EnumHTTPResponse');

        if (!$httpStatusString)
            throw new Exception('Error: An API resource must have an HTTP status property upon execution.');

        // Encode as JSON and render.
        $JSONString = json_encode($resourceData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        header($httpStatusString, true, $httpStatus);
        echo($JSONString);

    }
}
