<?php
/**
 * @author paulanderson
 * FileRendererTest.php
 * Initial commit: Paul Anderson, 4/28/2018
 *
 * Unit test for the FilePageView class.
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';

// Setting up some concrete DataResource objects for testing
class TestFileResource extends FileResource {

    var $testData;

    function getResource() {
        $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        return $this->testData;
    }
}


class FileRendererTest extends FileDependentTestCase {

    /**
     * @throws Exception
     * @runInSeparateProcess
     */
    function testRenderResource() {
        // ARRANGE
        $fileRenderer = new FileRenderer();
        $testFileResource = new TestFileResource();
        $testFileContents = 'Hello I am a text file.';
        $testFileName = 'file.txt';
        $this->makeTestFile($testFileName, $testFileContents);
        $testFilePath = $GLOBALS['TEST_FILE_PATH'];
        $testFileResource->testData = $testFilePath . $testFileName;

        // ACT
        $fileRenderer->renderResource($testFileResource);
        $output = ob_get_contents();

        // ASSERT
        $this->assertSame($testFileContents, $output);


    }
}
