<?php
/**
 * @author paulanderson
 * Initial Commit: 6/26/18
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';

class NestedDomDocumentComposerTest extends FileDependentTestCase {

    /**
     * @throws Exception
     */
    public function testComposeDomDocument() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();
        $ddc->resourceClient = new LocalResourceClient();
        $outerDomDocument = new DOMDocument();
        $outerNode = $outerDomDocument->createElement('div');
        $outerDomDocument->appendChild($outerNode);

        $outerContentElement = $outerDomDocument->createElement('div');
        $outerContentElement->setAttribute('id', 'toonces-content');
        $outerNode->appendChild($outerContentElement);
        $outerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'outer.html';
        $outerDomDocument->save($outerDomDocumentPath);


        $innerDomDocument = new DOMDocument();
        $innerContentElement = $innerDomDocument->createElement('div');
        $contentValue = 'this is the text content of the document';
        $innerContentElement->nodeValue = $contentValue;
        $innerContentElement->setAttribute('id', 'toonces-content');
        $innerDomDocument->appendChild($innerContentElement);
        $innerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'inner.html';
        $innerDomDocument->save($innerDomDocumentPath);

        $ddc->outerDomDocumentUrl = $outerDomDocumentPath;
        $ddc->innerDomDocumentUrl = $innerDomDocumentPath;

        // ACT
        $composedDocument = $ddc->composeDomDocument();
        $composedDocument->validate();
        $composedContentElement = $composedDocument->getElementById('toonces-content');
        //die($composedDocument->saveHTML());
        $composedContentValue = $composedContentElement->nodeValue;
        $composedOuterNodeList = $composedDocument->getElementsByTagName('outer_node');

        // ASSERT
        $this->assertSame($contentValue, $composedContentValue);
        $this->assertEquals(1, $composedOuterNodeList->count());
    }

    public function testOuterDomDocumentOnly() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();
        $outerDomDocument = new DOMDocument();
        $outerNode = new DOMNode();
        //$outerNode->nodeName = 'outer_node';
        $outerDomDocument->appendChild($outerNode);

        // ACT
        $composedDocument = $ddc->composeDomDocument();

        // ASSERT
        $this->assertSame($outerDomDocument, $composedDocument);
    }

    /**
     * @expectedException Exception
     * Programming error expected if object has inner and outer DOM documents without the toonces-content element
     */
    public function testNoContentElementException() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();
        $outerDomDocument = new DOMDocument();
        $innerDomDocument = new DOMDocument();

        // ACT
        $ddc->composeDomDocument();

        // ASSERT
        // (Expect Exception)
    }

    /**
     * @expectedException Exception
     */
    public function testNoOuterDocumentException() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();

        // ACT
        $ddc->composeDomDocument();

        // ASSERT
        // (Expect Exception)
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidOuterDocumentException() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();
        $ddc->outerDomDocument = 'Not a DOMDocument';

        // ACT
        $ddc->composeDomDocument();

        // ASSERT
        // (Expect Exception)

    }
}
