<?php
/**
 * @author paulanderson
 *
 * Renderer.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Abstract class defining the Renderer abstraction.
 *
 */

require_once LIBPATH . 'php/toonces.php';

abstract class Renderer implements iRenderer {

    /**
     * @param iResource $resource
     * @throws Exception
     */
    public function sendHttpStatusHeader($resource) {

        $httpStatus = $resource->getHttpStatus();
        $statusString = strval($httpStatus);
        if (!$statusString)
            throw new Exception('Programming Error: Resource objects must have the httpStatus variable set before rendering.');

        header($statusString, true, $httpStatus);

    }

}
