<?php
/**
 * @author paulanderson
 * Initial commit: 6/20/18
 */

require_once LIBPATH.'php/toonces.php';

class Toonces404DomDocumentResource extends DomDocumentResource {

    /**
     * @return DOMDocument
     * @throws Exception
     */
    function composeDomDocument() {
        $composer = new NestedDomDocumentComposer();
        $composer->resourceClient = new LocalResourceClient();
        $composer->outerDomDocumentUrl = LIBPATH . 'html/toonces_default_template.html';
        $composer->innerDomDocumentUrl = LIBPATH . 'html/toonces_404.html';

        $this->httpStatus = Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse');
        return $composer->composeDomDocument();

    }

}
