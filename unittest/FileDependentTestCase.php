<?php
/**
 * @author paulanderson
 * FileDependentTestCase.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Abstract class extending SqlDependentTestCase with file system dependency injection.
 * */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/SqlDependentTestCase.php';

abstract class FileDependentTestCase extends SqlDependentTestCase {

    /**
     * @throws Exception
     */
    function setUp() {
        parent::setUp();
        $this->checkFileFixture();
    }

    public function checkFileFixture() {
        // Performs a safety check on the test files directory.
        // If any files exist in the directory or if directory doesn't exist, it halts the test.
        $dir = $GLOBALS['TEST_FILE_PATH'];
        $ignoreFiles = array('..', '.');
        $dirData = scandir($dir);
        $filesExist = false;
        foreach ($dirData as $file) {
            if (!in_array($file, $ignoreFiles))
                $filesExist = true;
        }

        if ($filesExist)
            throw new Exception('Unit test exception: Test file directory for FileDependentTestCase doesn\'t exist or isn\'t empty.');
    }

    /**
     * @param string $fileName
     * @param string $fileContents
     */
    public function makeTestFile($fileName, $fileContents) {
        $dir = $GLOBALS['TEST_FILE_PATH'];
        file_put_contents($dir . $fileName, $fileContents);
    }


    public function destroyFileFixture() {
        // Deletes any files in the test directory.
        // Use with caution.
        $dir = $GLOBALS['TEST_FILE_PATH'];
        $dirData = scandir($dir);

        foreach ($dirData as $file) {
            // Delete the file.
            if ($file != '..' && $file != '.')
                unlink($GLOBALS['TEST_FILE_PATH'] . $file);
        }

    }

    function tearDown() {
        parent::tearDown();
        $this->destroyFileFixture();
    }
}
