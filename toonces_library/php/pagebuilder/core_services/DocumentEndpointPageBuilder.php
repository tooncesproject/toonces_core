<?php
/**
 * @author paulanderson
 *
 * DocumentEndpointPageBuilder.php
 * Initial Commit: Paul Anderson, 4/26/2018
 *
 * Provides an API endpoint for managing HTML files by instantiating a FileResource object.
 * Acquires the path to the HTML file store from toonces-config.xml
 *
 */

require_once LIBPATH.'php/toonces.php';

class DocumentEndpointPageBuilder extends APIPageBuilder {

    function buildPage() {

        // Acquire the path to the file bucket from toonces-config.xml
        $xml = new DOMDocument();
        $xml->load(ROOTPATH.'toonces-config.xml');

        $pathNode = $xml->getElementsByTagName('html_resource_path')->item(0);
        $path = $pathNode->nodeValue;

        $fileResource = new FileResource($this->pageViewReference);
        $fileResource->resourcePath = $path;
        $fileResource->requireAuthentication = true;

        array_push($this->resourceArray, $fileResource);
        return $this->resourceArray;

    }
}
