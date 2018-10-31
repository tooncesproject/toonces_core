<?php
/**
 * @author paulanderson
 * FileDependentTestCase.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Abstract class extending TooncesTestCase with file system dependency injection.
 * */

require __DIR__ . '/../vendor/autoload.php';
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
require_once __DIR__ . '/TooncesTestCase.php';

abstract class FileDependentTestCase extends TooncesTestCase {

    /** @var Filesystem */
    var $filesystem;


    function setUp() {
        parent::setUp();
        $fileSystemRootPath = $GLOBALS['TEST_FILE_PATH'];

        $fileSystemAdapter = new Local($fileSystemRootPath);
        $this->filesystem = new Filesystem($fileSystemAdapter);

        $contents = $this->filesystem->listContents('');

        if (!empty($contents))
            die("Test directory specified in phpunit.xml (" . $fileSystemRootPath . ") is not empty.");

    }


    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    function tearDown() {
        parent::tearDown();
        #$this->destroyFileFixture();

        // Deletes any files in the test directory.
        // Use with caution.
        foreach ($this->filesystem->listContents() as $object) {
            if ($object['type'] == 'file')
                $this->filesystem->delete($object['path']);

            if ($object['type'] == 'dir') {
                $this->filesystem->deleteDir($object['path']);
            }
        }

    }
}
