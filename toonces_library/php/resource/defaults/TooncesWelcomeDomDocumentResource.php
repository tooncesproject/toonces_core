<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

require_once LIBPATH.'php/toonces.php';

class TooncesWelcomeDomDocumentResource extends DomDocumentResource {

    /**
     * @return DOMDocument
     * @throws Exception
     */
    function composeDomDocument() {
        $composer = new NestedDomDocumentComposer();
        $composer->resourceClient = new LocalResourceClient();
        $composer->outerDomDocumentUrl = LIBPATH . 'html/toonces_default_template.html';
        $composer->innerDomDocumentUrl = LIBPATH . 'html/toonces_welcome.html';

        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $composer->composeDomDocument();

    }

}
