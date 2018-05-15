<?php
/**
 * @author paulanderson
 * TestFileResource.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Unit tests for the FileResource class.
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';

class TestFileResource extends FileDependentTestCase {

    /**
     * @expectedException Exception
     */
    function testGetResource() {
        // ARRANGE
        // Check the file fixture per FileDependentTestCase.
        $this->checkFileFixture();

        // Tear down (if exists) and rebuild the database fixture,.
        $this->destroyTestDatabase();
        $this->buildTestDatabase();

        // Proceed
        $pv = new FilePageview(1);
        $pv->setSQLConn($this->getConnection());
        $fr = new FileResource($pageView);
        $testData = 'look here is some data' .PHP_EOL;
        $fr->resourceData = $testData;
        $fr->httpMethod = 'OPTIONS';

        // ACT
        // Call without resourcePath - Expect exception.
        $noPathErrorState = false;
        try {
            $fr->getResource();
        } finally {
            $noPathErrorState = true;
        }

        // Call with bogus resource path - Except exception.
        $bogusPathErrorState = false;
        $fr->resourcePath = '/foo/foo/foo';
        try {
            $fr->getResource();
        } finally {
            $bogusPathErrorState = true;
        }

        // Call with OK resource path - Expect parent class (ApiResource) operations
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $validResponse = $fr->getResource();
        $resourceUri = $fr->resourceUri;

        // ASSERT
        // Call without resourcePath - Expect exception.
        $this->assertTrue($noPathErrorState);

        // Call with bogus resource path - Except exception.
        $this->assertTrue($bogusPathErrorState);

        // Call with OK resource path - Expect parent class (ApiResource) operations
        $this->assertSame($testData, $validResponse);

    }

    /**
     * @depends testGetResource
     */
    function testPutAction() {
        // ARRANGE
        // See testGetResource for fixture injection.
        // Create a non-admin user.
        $this->createNonAdminUser();

        // Create an unpublished page
        $pageId = $this-> createPage(false);

        // Instantiate a PageView object and dependencies
        $pageView = new FilePageview($pageId);
        $conn = $this->getConnection();
        $pageView->setSQLConn($conn);

        // Instantiate a FileResource object
        $filename = 'test.txt';
        $fr = new FileResource($pageView);
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fileData = 'Hello, I\'m a text file' . PHP_EOL;

        // Concatenate the file path
        $testFileVector = $GLOBALS['TEST_FILE_PATH'] . $filename;

        // Delete the test file, if it exists
        if (file_exists($testFileVector))
            unlink($testFileVector);

        // ACT

        // Attempt a PUT without authentication
        $this->unsetBasicAuth();
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fr->requestPath = 'http://example.com/'. $filename;
        $fr->resourceData = $fileData;
        $fr->putAction();
        $noAuthStatus = $fr->httpStatus;
        $noAuthFileExists = file_exists($testFileVector);

        // Attempt a PUT with authentication but filename is missing
        $this->setAdminAuth();
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fr->requestPath = 'http://example.com/';
        $fr->putAction();
        $noFilenameStatus = $fr->httpStatus;

        // Attempt a PUT with no file data
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fr->requestPath = 'http://example.com/'. $filename;
        $fr->resourceData = '';
        $fr->putAction();
        $noDataStatus = $fr->httpStatus;
        $noDataFileExists = file_exists($testFileVector);

        // Attempt a PUT where user does not explicity have access
        $this->setNonAdminAuth();
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fr->resourceData = $fileData;
        $fr->putAction();
        $nonAdminStatus = $fr->httpStatus;
        $nonAdminFileExists = file_exists($testFileVector);

        // Attempt a valid PUT for a new file
        $this->setAdminAuth();
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $fr->requestPath = 'http://example.com/'. $filename;
        $fr->resourceData = $fileData;
        $fr->putAction();
        $newFileStatus = $fr->httpStatus;
        $newFileExists = file_exists($testFileVector);
        $newFileData = file_get_contents($testFileVector);

        // Attepmpt a valid PUT for an existing file
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $changedFileData = 'hello, i\'m a different text file now.' . PHP_EOL;
        $fr->resourceData = $changedFileData;
        $fr->putAction();
        $existingFileStatus = $fr->httpStatus;
        $existingFileExists = file_exists($testFileVector);
        $existingFileData = file_get_contents($testFileVector);

        // ASSERT

        // Attempt a PUT without authentication
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAuthStatus);
        $this->assertFalse($noAuthFileExists);

        // Attempt a PUT with authentication but filename is missing
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $noFilenameStatus);

        // Attempt a PUT with no file data
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $noDataStatus);
        $this->assertFalse($noDataFileExists);

        // Attempt a PUT where user does not explicity have access
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);
        $this->assertFalse($nonAdminFileExists);

        // Attempt a valid PUT for a new file
        $this->assertEquals(Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse'), $newFileStatus);
        $this->assertTrue($newFileExists);
        $this->assertSame($fileData, $newFileData);

        // Attepmpt a valid PUT for an existing file
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $existingFileStatus);
        $this->assertTrue($existingFileExists);
        $this->assertSame($changedFileData, $existingFileData);

    }


    /**
     * @depends testPutAction
     */
    function testGetAction() {
        // ARRANGE
        // Create an unpublished page
        $pageId = $this-> createPage(false);

        // Instantiate a PageView object and dependencies
        $pageView = new FilePageview($pageId);
        $conn = $this->getConnection();
        $pageView->setSQLConn($conn);

        // Instantiate a FileResource object and dependencies
        $fr = new FileResource($pageView);
        $expectedFileData = 'hello, i\'m a different text file now.' . PHP_EOL;
        $filename = 'test.txt';
        $requestHost = 'http://example.com/';
        $requestPath = $requestHost . $filename;
        $expectedFileVector = $GLOBALS['TEST_FILE_PATH'] . $filename;
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];

        // ACT

        // Attempt a GET without authentication where authentication is required
        $this->unsetBasicAuth();
        $fr->requreAuthentication = true;
        $fr->requestPath = $requestPath;
        $noAuthResult = $fr->getAction();
        $noAuthStatus = $fr->httpStatus;


        // Attempt a GET by non-admin user without explicit access to the resource
        $this->setNonAdminAuth();
        $noAccessResult = $fr->getAction();
        $noAccessStatus = $fr->httpStatus;

        // Attempt a GET by an admin user where authentication is required
        $this->setAdminAuth();
        $adminAccessResult = $fr->getAction();
        $adminAccessStatus = $fr->httpStatus;
        $adminAccessContent = file_get_contents($adminAccessResult);

        // Attempt a GET without authentication where authentication is not required
        $fr->resourceData = null;
        $this->unsetBasicAuth();
        $fr->requreAuthentication = false;
        $publicAccessResult = $fr->getAction();
        $publicAccessStatus = $fr->httpStatus;
        $publicAccessContent = file_get_contents($publicAccessResult);

        // Attempt a GET on a file that doesn't exist
        $fr->resourceData = null;
        $bogusFileName = 'bogus_file.txt';
        $bogusFileRequest = $requestHost . $bogusFileName;
        $bogusFileVector = $GLOBALS['TEST_FILE_PATH'].$bogusFileName;

        // (Make sure the file doesn't actually exist from outside the test).
        if (file_exists($bogusFileVector))
            throw new Exception('Unit test error: Make sure file ' . $bogusFileVector . ' doesn\'t really exist before running unit tests.');

        $fr->requestPath = $bogusFileRequest;
        $bogusFileResult = $fr->getAction();
        $bogusFileStatus = $fr->httpStatus;


        // ASSERT

        // Attempt a GET without authentication where authentication is required
        $this->assertEmpty($noAuthResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAuthStatus);

        // Attempt a GET by non-admin user without explicit access to the resource
        $this->assertEmpty($noAccessResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAccessStatus);

        // Attempt a GET by an admin user where authentication is required
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $adminAccessStatus);
        $this->assertSame($expectedFileVector, $adminAccessResult);
        $this->assertsame($expectedFileData, $adminAccessContent);

        // Attempt a GET without authentication where authentication is not required
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $publicAccessStatus);
        $this->assertSame($expectedFileVector, $publicAccessResult);
        $this->assertsame($expectedFileData, $publicAccessContent);

        // Attempt a GET on a file that doesn't exist
        $this->assertEmpty($bogusFileResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $bogusFileStatus);

    }


    /**
     * @depends testGetAction
     */
    function testDeleteAction() {
        // ARRANGE
        // Create an unpublished page
        $pageId = $this-> createPage(false);

        // Instantiate a PageView object and dependencies
        $pageView = new FilePageview($pageId);
        $conn = $this->getConnection();
        $pageView->setSQLConn($conn);

        // Instantiate a FileResource object and dependencies
        $fr = new FileResource($pageView);
        $fr->resourcePath = $GLOBALS['TEST_FILE_PATH'];
        $filename = 'test.txt';
        $requestHost = 'http://example.com/';
        $requestPath = $requestHost . $filename;
        $expectedFileVector = $GLOBALS['TEST_FILE_PATH'] . $filename;
        $nonExistentFilename = 'nonexistentfile.txt';
        $nonExistentFileVector = $GLOBALS['TEST_FILE_PATH'] . $nonExistentFilename;
        $nonExistentRequestPath = $requestHost . $nonExistentFilename;
        // Test assumes the non-existent file doesn't exist - So let's check this.
        if (file_exists($nonExistentFileVector))
            throw new Exception('Unit test error: Cannot run file-dependent unit test if file ' . $nonExistentFileVector . ' exists.');

        // ACT
        // Attempt a DELETE without authentication
        $this->unsetBasicAuth();
        $fr->requestPath = $requestPath;
        $fr->deleteAction();
        $noAuthStatus = $fr->httpStatus;
        $noAuthFileExists = file_exists($expectedFileVector);

        // Attempt a DELETE with non-admin authentication without explicit access
        $this->setNonAdminAuth();
        $fr->deleteAction();
        $nonAdminStatus = $fr->httpStatus;
        $nonAdminFileExists = file_exists($expectedFileVector);

        // Attempt a DELETE on a file that doesn't exist
        $this->setAdminAuth();
        $fr->requestPath = $nonExistentRequestPath;
        $fr->deleteAction();
        $nonExistentFileStatus = $fr->httpStatus;

        // Attempt a valid DELETE
        $this->setAdminAuth();
        $fr->requestPath = $requestPath;
        $fr->deleteAction();
        $validStatus = $fr->httpStatus;
        $fileExistsAfterDelete = file_exists($expectedFileVector);

        // ASSERT
        // Attempt a DELETE without authentication
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAuthStatus);
        $this->assertTrue($noAuthFileExists);

        // Attempt a DELETE with non-admin authentication without explicit access
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);
        $this->assertTrue($nonAdminFileExists);

        // Attempt a DELETE on a file that doesn't exist
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonExistentFileStatus);

        // Attempt a valid DELETE
        $this->assertEquals(Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $validStatus);
        $this->assertFalse($fileExistsAfterDelete);

        // Tear Down
        $this->destroyTestDatabase();
        $this->destroyFileFixture();

    }
}
