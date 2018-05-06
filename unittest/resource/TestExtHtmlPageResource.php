<?php
/** 
 * @author paulanderson
 * TestExtHtmlPageResource.php
 * Unit tests for the class ExtHtmlPageResource
 * 
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';

// Dummy client for testing.
class DummyResourceClient implements iResourceClient {

    var $httpStatus;
    function getHttpStatus() {
        return $this->httpStatus;
    }
    
    function get($url, $username = null, $password = null, $paramHeaders = array()) {
        $content = file_get_contents($url);
        if ($content) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }
        return $content;
            
    }
    
    function put($url, $data, $username = null, $password = null, $headers = array()) {
        $success = file_put_contents($url, $data);
        if ($success) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }
            
        return $success;
    }
    
    function delete($url, $username = null, $password = null, $headers = array()) {
        $success = unlink($url);
        if ($success) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }
    }
}


class TestExtHtmlPageResource extends FileDependentTestCase {
    
    function testSetupClient() {
        // ARRANGE
        // ACT
        // ASSERT
        // Does not instantiate client if already set, returns 0
        // Instantiates client specified in resourceData, returns 0
        // Instantiates client from ext_html_page if pageId parameter set, returns 0
        
    }
    
    /**
     * @expectexception Error
     */
    function testSetupClientError() {
        // ARRANGE
        // ACT
        // ASSERT
        // Returns 1 if input is invalid
        // Sets http status 400 if invalid
    }
    
    function testPostAction() {
        // ARRANGE
        // clear and build fixtures.
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        $this->checkFileFixture();
        
        // Instantiate a client 
        $client = new DummyResourceClient();
        
        // Instantiate an ExtHtmlPageResource and dependencies.
        $pageId = $this->createPage(false);
        $pageView = new JsonPageView($pageId);
        $epr = new ExtHtmlPageResource($pageView);
        $epr->client = $client;
        
        $url = $GLOBALS['TEST_FILE_PATH'];
        $pageHtml = '<html><p>Hello!</p></html>';
        
        // ACT
        // ASSERT
        // Unauthenticated POST returns 401
        // Unauthenticated POST doesn't copy any files
        // null body POST returns 400
        // Authenticated POST creates page
        // Authenticated POST creates matching record in ext_html_file
        // Authenticated POST places matching file in the file resource directory
              
    }
    
    function testPutAction() {
        // ARRANGE
        
        
        // ACT
        // ASSERT
        // Unauthenticated PUT returns 401
        // Unauthenticated PUT doesn't copy any files
        // Authenticated PUT updates record in ext_html_file
        // Authenticated PUT creates matching file in the file resource directory
        // Authenticated PUT returns 200 or 201 HTTP status.
    }
    
    
    function testGetAction() {
        // ARRANGE
        // ACT
        // ASSERT
        // Unauthenticated GET returns 401
        // Autheticated GET appends htmlPath to result
    }
    
    
    function testDeleteAction() {
        // ARRANGE
        // ACT
        // ASSERT
        // Unauthenticated DELETE does not delete file
        // Authenticated DELETE deletes file
        // Authenticated DELETE deletes page record
        // Authenticated DELETE returns 204
        
    }
    
}