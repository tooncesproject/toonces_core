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
        // Get SQL connection
        $conn = $this->getConnection();
        // Create a page and insert an associated record in ext_html_page
        $pageId = $this->createPage(true);
        $sql = <<<SQL
        INSERT INTO ext_html_page
            (page_id, html_path, client_class)
        VALUES
            (:pageId, 'foo', 'DummyResourceClient')
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('pageId' => $pageId));
        
        // Instantiate an ExtHtmlPageResource and dependencies
        $pageView = new JsonPageView($pageId);
        $client = new DummyResourceClient();
        $ehpr = new ExtHtmlPageResource($pageView);
        
        // Mock up some data
        $data = array('clientClass' => 'DummyResourceClient');
        
        // ACT
        // Does not instantiate client if already set, returns 0
        $frc = new FileResourceClient($pageView);
        $ehpr->client = $frc;
        $ehpr->resourceData = $data;
        $ehpr->setupClient(null);
        $existingClass = $ehpr->client::class;
        
        // Instantiates client specified in resourceData, returns 0
        unset($ehpr->client);
        $ehpr->setupClient(null);
        $resourceDataClass = $ehpr->client::class;
        
        // Instantiates client from ext_html_page if pageId parameter set, returns 0
        unset($ehpr->client);
        unset($ehpr->resourceData);
        $ehpr->setupClient($pageId);
        $databaseClass = $ehpr->client::class;
        
        // ASSERT
        // Does not instantiate client if already set, returns 0
        $this->assertSame('FileResourceClient', $existingClass);
        
        // Instantiates client specified in resourceData, returns 0
        $this->assertSame('DummyResourceClient', $resourceDataClass);
        
        // Instantiates client from ext_html_page if pageId parameter set, returns 0
        $this->assertSame('DummyResourceClient', $databaseClass);
    }
    
    /**
     * @expectedException Error
     */
    function testSetupClientError() {
        // ARRANGE
        // Get SQL connection
        $conn = $this->getConnection();
        // Create a page
        $pageId = $this->createPage(true);
        $pageView = new JsonPageView($pageId);
        // Instantiate ExternalHtmlPageRecord
        $ehpr = new ExtHtmlPageResource($pageView);
        
        // ACT
        // No client class has been set - should error out.
        $result = null;
        $resourceStatus = null;
        try{
            $result = $ehpr->setupClient(null);
            $resourceStatus = $ehpr->httpStatus;
        } catch (Exception $e) {
            // No action
        }
        
        // ASSERT
        // Returns 1 if input is invalid
        $this->assertEquals(1, $result);

        // Sets http status 400 if invalid
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $resourceStatus);
    }
    
    function testPostAction() {
        // ARRANGE
        // Get SQL connection
        $conn = $this->getConnection();
        
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
        $epr->urlPath = $url;
        $pageHtml = '<html><p>Hello!</p></html>';
        
        $badRequestBody = array(
             'ancestorPageId' => 1
            ,'pageTitle' => 'Hello World'
            ,'htmlBody' => null
            ,'clientClass' => 'DummyResourceClient'
        );
        
        $goodRequestBody = $badRequestBody;
        $goodRequestBody['htmlBody'] = $pageHtml;
        
        // ACT
        // Unauthenticated POST returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $goodRequestBody;
        $noAuthResult = $epr->postAction();
        $noAuthStatus = $epr->httpStatus;
        
        // Unauthenticated POST doesn't copy any files
        $noFiles = scandir($url);
        
        // null body POST returns 400
        $this->setAdminAuth();
        $epr->resourceData = $badRequestBody;
        $nullBodyResult = $epr->postAction();
        $nullBodyStatus = $epr->httpStatus;
        
        // Authenticated POST creates page
        $epr->resourceData = $goodRequestBody;
        $goodResult = $epr->postAction();
        $goodStatus = $epr->httpStatus;

        // Authenticated POST creates matching record in ext_html_file
        $pageId = key($goodResult);
        $sql = "SELECT html_path FROM ext_html_page WHERE page_id = :pageId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $pageId]);
        $result = $stmt->fetchAll();
        $databaseUrl = $result[0]['html_path'];
        
        // Authenticated POST places matching file in the file resource directory
        $fileUrl = $goodResult['fileUrl'];
        
        // File contents matches input
        $fileContents = file_get_contents($databaseUrl);
        
        
        // ASSERT
        // Unauthenticated POST returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $noAuthStatus);
        
        // Unauthenticated POST doesn't copy any files
        $this->assertEmpty($noFiles);

        // null body POST returns 400
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $nullPostBody);
        
        // Authenticated POST creates page
        $this->assertEquals(Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse'), $goodStatus);

      
        // Authenticated POST creates matching record in ext_html_file
        $this->assertSame($fileUrl, $databaseUrl);
        
        // Authenticated POST places matching file in the file resource directory
        $this->assertFileExists($databaseUrl);
        
        // File contents matches input
        $this->assertSame($pageHtml, $fileContents);
    }
    
    /**
     * @depends testPostAction
     */
    function testPutAction() {
        // ARRANGE
        // Query the database for the file/page created by testPostAction
        $conn = $this->getConnection();
        $sql = <<<SQL
        SELECT
             page_id
            ,html_path
        FROM
            ext_html_page
        ORDER BY
            ext_html_page_id DESC
        LIMIT 1
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute;
        $result = $stmt->fetchAll();
        $pageId = $result[0]['page_id'];
        $dbHtmlPathBefore = $result[0]['html_path'];
        
        // Instantiate an ExtHtmlPageResource and dependencies.
        $pageView = new JsonPageView($pageId);
        $epr = new ExtHtmlPageResource($pageView);
        $client = new DummyResourceClient();
        $epr->client = $client;
        
        $url = $GLOBALS['TEST_FILE_PATH'];
        $epr->urlPath = $url;
        $pageHtml = '<html><p>Hello! I am different now.</p></html>';
        $putBody = array(['htmlBody'] => $pageHtml);

        $filesBefore = scandir($url);
        $countFilesBefore = count($filesBefore);
        
        // ACT
        // Unauthenticated PUT returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $putBody;
        $unauthResult = $epr->putAction();
        $unauthStatus = $epr->httpStatus;
        
        // Unauthenticated PUT doesn't copy any files
        $noAuthfiles = scandir($url);
        $noAuthFilesCount = count($noAuthfiles);
        
        // Authenticated PUT updates record in ext_html_file
        $this->setAdminAuth();
        $authResult = $epr->putAction();
        
        $sql = "SELECT html_path FROM ext_html_page WHERE page_id = :pageId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $pageId]);
        $result = $stmt->fetchAll();
        $dbHtmlPathAfter = $result[0]['html_path'];
        $returnedHtmlPath = $authResult['fileUrl'];
        
        // Authenticated PUT creates matching file in the file resource directory
        $fileExists = file_exists($dbHtmlPathAfter);
        $fileContents = file_get_contents($dbHtmlPathAfter);
        
        // Authenticated PUT returns 200 HTTP status.
        $authStatus = $epr->httpStatus;
        
        
        // ASSERT
        // Unauthenticated PUT returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $unauthStatus);

        // Unauthenticated PUT doesn't copy any files
        $this->assertEquals($countFilesBefore, $noAuthFilesCount);
        
        // Authenticated PUT updates record in ext_html_file
        $this->assertNotEmpty($dbHtmlPathAfter);
        $this->assertNotSame($dbHtmlPathBefore, $dbHtmlPathAfter);
        $this->assertSame($dbHtmlPathAfter, $returnedHtmlPath);
        
        // Authenticated PUT creates matching file in the file resource directory
        $this->assertTrue($fileExists);
        $this->assertSame($pageHtml, $fileContents);
        
        
        // Authenticated PUT returns 200 HTTP status.
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $authStatus);
    }
    
    /**
     * @depends testPutAction
     */
    function testGetAction() {
        // ARRANGE// Query the database for the file/page created by testPostAction
        $conn = $this->getConnection();
        $sql = <<<SQL
        SELECT
             page_id
        FROM
            ext_html_page
        ORDER BY
            ext_html_page_id DESC
        LIMIT 1
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute;
        $result = $stmt->fetchAll();
        $pageId = $result[0]['page_id'];
        
        // ACT
        // ASSERT
        // Unauthenticated GET returns 401
        // Autheniticated GET includes the fields inherited from PageDataResource
        // Autheticated GET appends fileUrl to result

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