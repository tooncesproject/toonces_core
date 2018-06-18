<?php
/**
 * @author paulanderson
 * Initial Commit: 6/17/18
 *
 * To implement:
 *      Override render(), setting the inner/outer DomDocumentPath vars.
 *          Inner is optional.
 *          After setting the variables, call parent::render().
 *
 */


abstract class SimpleDomDocumentResource extends DomDocumentResource implements iDomDocumentResource {

    /**
     * @var string
     */
    var $outerDomDocumentPath;

    /**
     * @var string
     */
    var $innerDomDocumentPath;

    public function render() {

        $outerDomDocument = new DOMDocument();
        $outerDomDocument->load($this->outerDomDocumentPath);
        $innerContentElement = null;

        if ($this->innerDomDocumentPath) {
            $innerDomDocument = new DOMDocument();
            $innerDomDocument->load($this->innerDomDocumentPath);
            $innerContentElement = $innerDomDocument->documentElement;
        }

        $contentElement = $outerDomDocument->getElementById('toonces-content');

        if ($contentElement && $innerContentElement)
            $contentElement ->appendChild($innerContentElement);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $outerDomDocument;
    }

}
