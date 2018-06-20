<?php
/**
 * @author paulanderson
 * Initial commit: 6/20/18
 */

abstract class NestedDomDocumentResource extends DomDocumentResource implements iNestedDomDocumentResource
{

    /**
     * @var string
     * tryna make this testable
     */
    public $htmlResourcePath;

    /**
     * @return DOMDocument
     *
     * Default behavior is to use the template specified in toonces-config.xml.
     * Override to customize the outer template.
     */
    private function getTemplateDomDocument()
    {

        $configXml = new DOMDocument();
        $configXml->load(ROOTPATH . 'toonces-config.xml');

        if (!$this->htmlResourcePath) {
            $pathNode = $configXml->getElementsByTagName('html_resource_path')->item(0);
            $this->htmlResourcePath = $pathNode->nodeValue;
        }

        $fileNameNode = $configXml->getElementsByTagName('default_page_template')->item(0);
        $fileName = $fileNameNode->nodeValue;

        $templateFilePath = $this->htmlResourcePath . $fileName;

        $templateDomDocument = new DOMDocument();
        $templateDomDocument->load($templateFilePath);

        return $templateDomDocument;
    }

    public function composeDomDocument()
    {

        $templateDomDocument = $this->getTemplateDomDocument();
        $innerDomDocument = $this->getInnerDomDocument();

        $contentElement = $templateDomDocument->getElementById('toonces-content');
        $innerContentElement = $innerDomDocument->documentElement;

        if ($contentElement && $innerContentElement)
            $contentElement->appendChild($innerContentElement);

        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $templateDomDocument;

    }

}
