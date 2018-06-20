<?php
/**
 * @author paulanderson
 * Initial commit: 6/20/18
 */

class Toonces404DomDocumentResource extends NestedDomDocumentResource {

    function getInnerDomDocument() {
        $configXml = new DOMDocument();
        $configXml->load(ROOTPATH . 'toonces-config.xml');

        $pathNode = $configXml->getElementsByTagName('html_resource_path')->item(0);
        $path = $pathNode->nodeValue;

        $fileName = 'toonces_404.html';
        $innerDomDocument = new DOMDocument();
        $innerDomDocument->load($path . $fileName);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');

        return $innerDomDocument;
    }

}
