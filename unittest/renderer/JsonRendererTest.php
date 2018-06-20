<?php
/**
 * @author paulanderson
 * JsonRendererTest.php
 * Initial Commit: Paul Anderson, 4/25/2018
 *
 * // Unit test for JsonRenderer class
 *
*/

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

// Setting up some concrete DataResource objects for testing
class TestDataResource extends DataResource {
    function getResource() {
        $testData = array('foo' => 'bar');
        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $testData;
    }
}


class JsonRendererTest extends TestCase {

    /**
     * @runInSeparateProcess
     * @throws Exception
     */
    function testRenderResource() {
        // ARRANGE
        $jpv = new JsonRenderer();
        $goodDataResource = new TestDataResource();
        $testData = array('foo' => 'bar');
        $expectedString = json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // ACT
        $jpv->renderResource($goodDataResource);

        // ASSERT
        $this->expectOutputString($expectedString);



    }
}
