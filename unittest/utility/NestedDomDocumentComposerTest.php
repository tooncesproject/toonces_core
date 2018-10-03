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

        $outerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'outer.html';
        $innerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'inner.html';

        $outerDocumentString = <<<HTML
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
        <html><body><div><div id="toonces-content"></div></div>
        </body></html>
HTML;

        $innerDocumentString = <<<HTML
        <div id="toonces-content">this is the text content of the document</div>
HTML;

        $contentValue = 'this is the text content of the document';

        file_put_contents($innerDomDocumentPath, $innerDocumentString);
        file_put_contents($outerDomDocumentPath, $outerDocumentString);


        $ddc->outerDomDocumentUrl = $outerDomDocumentPath;
        $ddc->innerDomDocumentUrl = $innerDomDocumentPath;

        // ACT
        $composedDocument = $ddc->composeDomDocument();

        // get it with xpath
        $xpath = new DOMXPath($composedDocument);
        $xpathQuery = "//div[@id='toonces-content']";
        $domNodeList = $xpath->query($xpathQuery);
        $composedContentElement = $domNodeList->item(0);

        $composedContentValue = $composedContentElement->nodeValue;

        // ASSERT
        $this->assertSame($contentValue, $composedContentValue);
    }

    /**
     * @throws Exception
     */
    public function testOuterDomDocumentOnly() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();
        $ddc->resourceClient = new LocalResourceClient();



        $outerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'outer.html';
        $outerDocumentString = <<<HTML
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
        <html><body><div><div id="toonces-content"></div></div>
        </body></html>
HTML;

        $originalDomDocument = new DOMDocument();
        $originalDomDocument->loadHTML($outerDocumentString);
        $originalDomHtml = $originalDomDocument->saveHTML();



        $outerDomDocumentPath = $GLOBALS['TEST_FILE_PATH'] . 'outer.html';
        file_put_contents($outerDomDocumentPath, $outerDocumentString);

        $ddc->outerDomDocumentUrl = $outerDomDocumentPath;

        // ACT
        $composedDocument = $ddc->composeDomDocument();
        $htmlAfter = $composedDocument->saveHTML();

        // ASSERT
        //$this->assertSame($outerDomDocument, $composedDocument);
        $this->assertSame($originalDomHtml, $htmlAfter);
    }

    /**
     * @expectedException Exception
     * Programming error expected if object has inner and outer DOM documents without the toonces-content element
     */
    public function testNoContentElementException() {
        // ARRANGE
        $ddc = new NestedDomDocumentComposer();

        // TODO test with document URIs
        //$outerDomDocument = new DOMDocument();
        //$innerDomDocument = new DOMDocument();

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
