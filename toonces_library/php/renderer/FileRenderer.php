<?php
/**
 * @author paulanderson
 * FileRenderer.php
 * Initial commit: Paul Anderson, 4/26/2018
 *
 * Renderer extension for handling transfers of files from a vector.
 * Suitable for small throughput only; doesn't kill output buffering.
*/

require_once LIBPATH . 'php/toonces.php';

class FileRenderer extends Renderer implements iRenderer {

    /**
     * @param iFileResource $resource
     * @throws Exception
     * @return string
     */
    public function renderResource($resource) {

        $resourcePath = $resource->getResource();

        $this->sendHttpStatusHeader($resource);

        // If applicable - Say, this is a GET request - Start the transfer.
        if ($resourcePath) {
            $headerStr = "Content-Type: application/octet-stream";
            header($headerStr);

            flush();
            readfile($resourcePath);

        }

    }
}
