<?php
/**
 * @author paulanderson
 * TestJsonPageView.php
 * Initial Commit: Paul Anderson, 4/25/2018
 * 
 * // Unit test for JsonPageView class
 * 
*/

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
//require_once __DIR__ . '../../SqlDependentTestCase.php';

// Setting up some concrete DataResource objects for testing
class GoodDataResource extends DataResource {
    function getResource() {
        $testData = array('foo' => 'bar');
        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPStatus');
        return $testData;
    }
}

class BadDataResource extends DataResource {
    function getResource() {
        $testData = array('foo' => 'bar');
        $this->httpStatus = null;
        return $testData;
    }
}

class TestJsonPageView extends TestCase {

    /**
     * @expectedException Exception
     */
    function testRenderPage() {
        // ARRANGE
        $jpv = new JsonPageView(1);
        $goodDataResource = new GoodDataResource($jpv);
        $badDataResource = new BadDataResource($jpv);
        $badOutput = null;
        $goodOutput = null;

        // ACT
        // With invalid DataResource
        $jpv->dataObjects = array(0 => $badDataResource );
        
        $errorState = false;
        try {
            $badOutput = $jpv->renderPage();
        } finally {
            $errorState = true;
        }
        
        // with valid DataResource
        $jpv->dataObjects = array(0 => $goodDataResource );
        $goodOutput = $jpv->renderPage();
        json_decode($goodOutput);
        
        // ASSERT
        $this->assertNull($badOutput);
        $this->assertTrue($errorState);
        $this->assertTrue(is_array($goodOutput));
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
        
    }
}