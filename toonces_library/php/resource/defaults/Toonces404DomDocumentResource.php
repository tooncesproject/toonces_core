<?php
/**
 * @author paulanderson
 * Initial commit: 6/20/18
 */

class Toonces404DomDocumentResource extends NestedDomDocumentResource {

    function getInnerDomDocument() {

        $fileName = LIBPATH . 'html/toonces_404.html';
        $innerDomDocument = new DOMDocument();
        $innerDomDocument->load($fileName);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');

        return $innerDomDocument;
    }

}
