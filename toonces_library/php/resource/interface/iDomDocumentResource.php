<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

interface iDomDocumentResource extends iResource {

    /**
     * @return DOMDocument
     */
    function composeDomDocument();

    /**
     * @return DOMDocument
     */
    public function getResource();

}
