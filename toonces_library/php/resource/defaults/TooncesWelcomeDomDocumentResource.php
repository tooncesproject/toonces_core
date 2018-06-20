<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

class TooncesWelcomeDomDocumentResource extends NestedDomDocumentResource {

    function getInnerDomDocument() {
        $configXml = new DOMDocument();
        $configXml->load(ROOTPATH . 'toonces-config.xml');

        $pathNode = $configXml->getElementsByTagName('html_resource_path')->item(0);
        $path = $pathNode->nodeValue;

        $fileName = 'toonces_welcome.html';
        $innerDomDocument = new DOMDocument();
        $innerDomDocument->load($path . $fileName);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');

        return $innerDomDocument;
    }

}
