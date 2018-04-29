<?php
/**
 * @author paulanderson
 * TestFilePageView.php
 * Initial commit: Paul Anderson, 4/28/2018
 *
 * Unit test for the FilePageView class.
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

// Setting up some concrete DataResource objects for testing
class GoodFileResource extends FileResource {
    function getResource() {
        $testData = array('example.com/barf');
        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPStatus');
        return $testData;
    }
}

class BadFileResource extends FileResource {
    function getResource() {
        $testData = array('example.com/barf');
        $this->httpStatus = null;
        return $testData;
    }
}

class TestFilePageView extends TestCase {


    /**
     * @expectedException Exception
     */
    function testRenderPage() {
        // Notes: Without a wrapper, PHPUnit doesn't include a way to test the
        // readfile() output of this class.
        // We'll lean on integration testing and/or trust that the PHP-native functions
        // actually work.
        // ARRANGE
        $jpv = new FilePageview(1);
        $goodFileResource = new GoodFileResource($jpv);
        $badFileResource = new BadFileResource($jpv);
        $badOutput = null;
        $goodOutput = null;

        // ACT
        // With invalid DataResource
        $jpv->dataObjects = array(0 => $badFileResource );

        $errorState = false;
        try {
            $badOutput = $jpv->renderPage();
        } finally {
            $errorState = true;
        }

        // with valid DataResource
        $jpv->dataObjects = array(0 => $goodFileResource );
        $goodOutput = $jpv->renderPage();

        // ASSERT
        $this->assertNull($badOutput);
        $this->assertTrue($errorState);
        $this->assertSame('example.com/barf', $goodOutput);

    }
}