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

    public function destroyFileFixture() {
        // Deletes any files in the test directory.
        // Use with caution. You should run checkFileFixture at the start of
        // your unit test to ensure you're not deleting anything by accident here -
        // Then after tests have run, call destroyFileFixture to clear any files
        // created by your test.
        $dir = $GLOBALS['TEST_FILE_PATH'];
        $dirData = scandir($dir);

        foreach ($dirData as $file) {
            // Delete the file.
            if ($file != '..' && $file != '.')
                unlink($file);
        }

    }
}
