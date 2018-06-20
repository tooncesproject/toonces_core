<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

class TooncesWelcomeDomDocumentResource extends NestedDomDocumentResource {

    function getInnerDomDocument() {

        $fileName = LIBPATH . 'html/toonces_welcome.html';
        $innerDomDocument = new DOMDocument();
        $innerDomDocument->load($fileName);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');

        return $innerDomDocument;
    }

}
