<?php
/**
 * @author paulanderson
 * LocalResourceClientTest.php
 * Unit tests for the class LocalResourceClient
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';

class LocalResourceClientTest extends FileDependentTestCase {
    

    function testPut() {
        // ARRANGE
        // Set up file fixture
        $this->checkFileFixture();
        
        // Set up client
        $client = new LocalResourceClient();
        
        $data = "hi i am file data";
        $path = $GLOBALS['TEST_FILE_PATH'];
        $url = $path . 'test_local_resource_client.txt';
        
        // ACT
        $response = $client->put($url, $data);
        
        // ASSERT
        $this->assertFileExists($url);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $client->getHttpStatus());
        
    }
    
    /**
     * @depends testPut
     */
    function testGet() {
        // ARRANGE
        // Set up client
        $client = new LocalResourceClient();
        
        $data = "hi i am file data";
        $path = $GLOBALS['TEST_FILE_PATH'];
        $url = $path . 'test_local_resource_client.txt';
        
        // ACT
        $response = $client->get($url);
        
        // ASSERT
        $this->assertSame($data, $response);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $client->getHttpStatus());
        
    }
    
    /**
     * @depends testGet
     */
    function testDelete() {
        // ARRANGE
        // Set up client
        $client = new LocalResourceClient();

        $path = $GLOBALS['TEST_FILE_PATH'];
        $url = $path . 'test_local_resource_client.txt';
        
        // ACT
        $response = $client->delete($url);
        
        // ASSERT
        $this->assertFileNotExists($url);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $client->getHttpStatus());
        
        // Tear down
        $this->destroyFileFixture();
    }
    
}
