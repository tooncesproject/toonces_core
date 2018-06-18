<?php
/**
 * @author paulanderson
 *
 * DomDocumentRenderer.php
 * Initial commit: Paul Anderson, 6/15/18
 *
 * Renderer for resources providing a DOMDocument object.
 * Echos the DOM as a string.
 *
 */

require_once LIBPATH . 'php/toonces.php';

class DomDocumentRenderer extends Renderer implements iRenderer {

    /**
     * @param iDomDocumentResource $resource
     * @throws Exception
     */
    public function renderResource($resource) {

        $domDocument = $resource->getResource();
        $this->sendHttpStatusHeader($resource);

        echo $domDocument->saveHTML();
    }
}
