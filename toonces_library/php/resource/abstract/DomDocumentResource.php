<?php
/**
 * @author paulanderson
 *
 * DomDocumentResource.php
 * Initial commit: Paul Anderson, 6/15/18
 *
 * Abstract Resource subclass defining the basic behavior of resources providing a DOMDocument object.
 *
 */

require_once LIBPATH . 'php/toonces.php';

abstract class DomDocumentResource extends Resource implements iDomDocumentResource {

    /**
     * @throws Exception
     */
    public function render() {
        $renderer = new DomDocumentRenderer();

        $renderer->renderResource($this);
    }
}
