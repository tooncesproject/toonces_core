<?php
/**
 * @author paulanderson
 * Initial commit: Paul Anderson, 1/24/2018
 *
 * Converts to JSON and renders data from DataResource objects.
 *
 */

require_once LIBPATH . 'php/toonces.php';

class JsonRenderer extends Renderer implements iRenderer
{

    /**
     * @param iDataResource $resource
     * @throws Exception
     */
    public function renderResource($resource) {

        // Execute the object
        $resourceData = $resource->getResource();

        $this->sendHttpStatusHeader($resource);

        // Encode as JSON and render.
        $JSONString = json_encode($resourceData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo($JSONString);

    }
}
