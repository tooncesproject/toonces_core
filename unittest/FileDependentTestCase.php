<?php
/**
 * @author paulanderson
 * FileDependentTestCase.php
 * Initial commit: Paul Anderson, 4/27/2018
 * 
 * Abstract class extending SqlDependentTestCase with file system dependency injection.
 * */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

abstract class FileDependentTestCase extends SqlDependentTestCase {
    
    public function createFileFixture() {
        $dir = $GLOBALS['TEST_FILE_PATH'];
        $textStr = 'hello I"m a text string for a text file.';
        $textfileName = 'test.txt';

        mkdir($dir);
        $created = file_put_contents($dir . $textfileName, $textStr);
        return $created;
    }
    
    public function destroyFileFixture() {
        $dir = $GLOBALS['TEST_FILE_PATH'];
        $removed = rmdir($dir);
        return $removed;
    }
}