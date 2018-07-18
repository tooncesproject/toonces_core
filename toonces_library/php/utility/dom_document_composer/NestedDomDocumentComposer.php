<?php
/**
 * @author paulanderson
 * Initial Commit: 6/26/18
 */

class NestedDomDocumentComposer implements iDomDocumentComposer {

    /** @var string */
    public $outerDomDocumentUrl;

    /** @var string */
    public $innerDomDocumentUrl;

    /** @var string */
    public $clientUsername;

    /** @var string */
    public $clientPassword;

    /** @var array */
    public $clientHeaders;

    /** @var iResourceClient */
    public $resourceClient;

    /** @var DOMDocument */
    public $outerDomDocument;

    /** @var DOMDocument */
    public $innerDomDocument;


    /**
     * @return DOMDocument
     * @throws Exception
     */
    public function composeDomDocument() {

        $this->outerDomDocument = $this->loadDomDocumentFromFile($this->outerDomDocumentUrl);
        //echo $this->outerDomDocument->saveHTML();

        // TODO remove this
        $this->outerDomDocument->save($this->outerDomDocumentUrl);


        if ($this->innerDomDocumentUrl)
            $this->innerDomDocument = $this->loadDomDocumentFromFile($this->innerDomDocumentUrl);

        // TODO remove this
        $this->outerDomDocument->save($this->outerDomDocumentUrl);


        //$this->checkContentElementIds();
        // TODO remove this
        $this->outerDomDocument->save($this->outerDomDocumentUrl);


        $this->validateDomDocuments();

        // TODO remove this
        $this->outerDomDocument->save($this->outerDomDocumentUrl);



        if ($this->innerDomDocument) {
            $innerContentElement = $this->innerDomDocument->getElementById('toonces-content');
            //die(var_dump($innerContentElement->textContent));

            // TODO remove this
            $this->outerDomDocument->save($this->outerDomDocumentUrl);



            $outerContentElement = $this->outerDomDocument->getElementById('toonces-content');

            // TODO remove this
            $this->outerDomDocument->save($this->outerDomDocumentUrl);



            $outerParentElement = $outerContentElement->parentNode;

            // TODO remove this
            $this->outerDomDocument->save($this->outerDomDocumentUrl);


            $importedElement = $this->outerDomDocument->importNode($innerContentElement, true);

            // TODO remove this
            $this->outerDomDocument->save($this->outerDomDocumentUrl);

            $outerParentElement->replaceChild($importedElement,$outerContentElement);
        }

        // TODO remove this
        $this->outerDomDocument->save($this->outerDomDocumentUrl);

        return $this->outerDomDocument;

    }

    /**
     * @param string $fileUrl
     * @throws Exception
     * @return DOMDocument;
     */
    private function loadDomDocumentFromFile($fileUrl) {

        if (!$this->resourceClient)
            throw new Exception('Programming error: NestedDomDocumentComposer::composeDomDocument was called without the $resourceClient property being set.');

        $domString = $this->resourceClient->get($fileUrl, $this->clientUsername, $this->clientPassword, $this->clientHeaders);
        $domDocument = new DOMDocument();

        // Will this help?
        $domDocument->validateOnParse = true;

        $domDocument->loadHTML($domString);
        //$domDocument->loadXML($domString);
        //$domDocument->saveXML();
        $domDocument->saveHTML();
        return $domDocument;
    }

    /**
     * @throws Exception
     */
    private function checkContentElementIds() {
        if ($this->innerDomDocument && $this->outerDomDocument) {
            $innerContentElement = $this->innerDomDocument->getElementById('toonces-content');
            $outerContentElement = $this->outerDomDocument->getElementById('toonces-content');

            if (!$innerContentElement || !$outerContentElement)
                throw new Exception('Programming Error: NestedDomDocumentComposer requires both its $innerDomDocument and $outerDomDocument DOMDocument properties to contain an element with ID "toonces-content" if both are set.');
        }

    }

    /**
     * @throws Exception
     */
    private function validateDomDocuments() {
        if (!is_subclass_of($this->outerDomDocument, DOMNode::class))
            throw new Exception('Programming error: property $outerDomDocument must be set and must be an object subclassed from DOMNode.');

        if (isset($this->innerDomDocument)) {
            if (!is_subclass_of($this->innerDomDocument, DOMNode::class))
                throw new Exception('Programming error: Property $innerDomDocument must be an object subclassed from DOMNode.');
        }
    }
}
