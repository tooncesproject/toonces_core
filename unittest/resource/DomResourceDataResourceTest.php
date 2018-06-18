<?php
/**
 * @author paulanderson
 * DomResourceDataResourceTest.php.php
 * Unit tests for the class DomResourceDataResource
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../FileDependentTestCase.php';


class DomResourceDataResourceTest extends FileDependentTestCase {

    function testSetupClient() {
        // ARRANGE
        // Get SQL connection
        $conn = $this->getConnection();

        // clear and build fixtures.
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        $this->checkFileFixture();

        // Create a resource and insert an associated record in ext_html_page
        $resourceId = $this->createPage(true);
        $sql = <<<SQL
        INSERT INTO ext_html_page
            (resource_id, html_path, client_class)
        VALUES
            (:resourceId, 'foo', 'LocalResourceClient')
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('resourceId' => $resourceId));

        // Instantiate an DomResourceDataResource and dependencies
        $ehpr = new DomResourceDataResource();
        $ehpr->resourceId = $resourceId;
        $ehpr->conn = $conn;

        // Mock up some data
        $data = array('clientClass' => 'LocalResourceClient');

        // ACT
        // Does not instantiate client if already set, returns 0
        $resourceClient = new ResourceClient();
        $ehpr->client = $resourceClient;
        $ehpr->resourceData = $data;
        $ehpr->setupClients(null);
        $existingClass = get_class($ehpr->client);

        // Instantiates client specified in resourceData, returns 0
        unset($ehpr->client);
        $ehpr->setupClients(null);
        $resourceDataClass = get_class($ehpr->client);

        // Instantiates client from ext_html_page if resourceId parameter set, returns 0
        unset($ehpr->client);
        unset($ehpr->resourceData);
        $ehpr->setupClients($resourceId);
        $databaseClass = get_class($ehpr->client);

        // ASSERT
        // Does not instantiate client if already set, returns 0
        $this->assertSame('ResourceClient', $existingClass);

        // Instantiates client specified in resourceData, returns 0
        $this->assertSame('LocalResourceClient', $resourceDataClass);

        // Instantiates client from ext_html_page if resourceId parameter set, returns 0
        $this->assertSame('LocalResourceClient', $databaseClass);
    }


    /**
     * @depends testSetupClient
     */
    function testPostAction() {
        // ARRANGE
        // Get SQL connection
        $conn = $this->getConnection();

        // Instantiate a client
        $client = new LocalResourceClient();

        // Instantiate an DomResourceDataResource and dependencies.
        $resourceId = $this->createPage(false);
        $epr = new DomResourceDataResource();
        $epr->conn = $conn;
        $epr->resourceId = $resourceId;
        $epr->client = $client;

        $url = $GLOBALS['TEST_FILE_PATH'];
        $epr->urlPath = $url;
        $pageHtml = '<html><p>Hello!</p></html>';

        $badRequestBody = array(
             'ancestorResourceId' => 1
            ,'pageTitle' => 'Hello World'
            ,'htmlBody' => null
            ,'clientClass' => 'LocalResourceClient'
        );

        $pdrBadRequestBody = $badRequestBody;
        $pdrBadRequestBody['ancestorResourceId'] = 99999;
        $pdrBadRequestBody['htmlBody'] = $pageHtml;

        $goodRequestBody = $badRequestBody;
        $goodRequestBody['htmlBody'] = $pageHtml;


        // ACT
        // Count the number of files before
        $countFilesBefore = count(scandir($url));

        // Unauthenticated POST returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $goodRequestBody;
        $noAuthResult = $epr->postAction();
        $noAuthStatus = $epr->httpStatus;

        // Unauthenticated POST doesn't copy any files
        $noFiles = scandir($url);

        // Unauthenticated POST with invalid data per ResourceDataResource returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $pdrBadRequestBody;
        $pdrInvalidResult = $epr->postAction();
        $pdrInvalidStatus = $epr->httpStatus;

        // Unauthenticated POST wih invalid data doesn't copy any files
        $pdrNoFiles = scandir($url);

        // null body POST returns 400
        $this->setAdminAuth();
        $epr->resourceData = $badRequestBody;
        $nullBodyResult = $epr->postAction();
        $nullBodyStatus = $epr->httpStatus;

        // Authenticated POST creates resource
        $this->setAdminAuth();
        $epr->resourceData = $goodRequestBody;
        $goodResult = $epr->postAction();
        $goodStatus = $epr->httpStatus;

        // Authenticated POST creates matching record in ext_html_file
        $resourceId = key($goodResult);
        $sql = "SELECT html_path FROM ext_html_page WHERE resource_id = :resourceId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
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
        $this->assertEquals($countFilesBefore, count($noFiles));

        // Unauthenticated POST with invalid data per ResourceDataResource returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $pdrInvalidStatus);

        // Unauthenticated POST wih invalid data doesn't copy any files
        $this->assertEquals($countFilesBefore, count($pdrNoFiles));

        // null body POST returns 400
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $nullBodyStatus);

        // Authenticated POST creates resource
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
        // Query the database for the file/resource created by testPostAction
        $conn = $this->getConnection();
        $sql = <<<SQL
        SELECT
             resource_id
            ,html_path
        FROM
            dom_resource
        ORDER BY
            ext_html_page_id DESC
        LIMIT 1
SQL;

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $resourceId = $result[0]['resource_id'];
        $dbHtmlPathBefore = $result[0]['html_path'];

        // Instantiate an DomResourceDataResource and dependencies.
        $epr = new DomResourceDataResource();
        $client = new LocalResourceClient();
        $epr->client = $client;
        $epr->conn = $conn;
        $epr->resourceId = $resourceId;
        $epr->parameters['id'] = strval($resourceId);

        $url = $GLOBALS['TEST_FILE_PATH'];
        $epr->urlPath = $url;
        $pageHtml = '<html><p>Hello! I am different now.</p></html>';
        $putBody = array('htmlBody' => $pageHtml);
        $filesBefore = scandir($url);
        $countFilesBefore = count($filesBefore);

        // Mock up an invalid PUT body
        $invalidBody = array(
          'pageTitle' => 6
        );

        // ACT
        // Unauthenticated PUT returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $putBody;
        $unauthResult = $epr->putAction();
        $unauthStatus = $epr->httpStatus;

        // Unauthenticated PUT doesn't copy any files
        $noAuthfiles = scandir($url);
        $noAuthFilesCount = count($noAuthfiles);

        // Unauthenticated PUT with invalid data returns 401
        $this->unsetBasicAuth();
        $epr->resourceData = $invalidBody;
        $unauthInvalidResult = $epr->putAction();
        $unauthInvalidStatus = $epr->httpStatus;

        // Unauthenticated PUT with invalid data doesn't change extHtmlPage
        $sql = <<<SQL
        SELECT html_path
        FROM ext_html_page
        WHERE resource_id = :resourceId
SQL;

        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
        $result = $stmt->fetchAll();
        $unauthHtmlPath = $result[0]['html_path'];

        // Authenticated PUT updates record in ext_html_file
        // Sleep 1 second so there will be a difference
        sleep(2);
        $this->setAdminAuth();
        $epr->resourceData = $putBody;
        $authResult = $epr->putAction();

        $sql = "SELECT html_path FROM ext_html_page WHERE resource_id = :resourceId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
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

        // Unauthenticated PUT with invalid data returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $unauthInvalidStatus);

        // Unauthenticated PUT with invalid data doesn't change extHtmlPage
        $this->assertSame($dbHtmlPathBefore, $unauthHtmlPath);

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
     * @depends testPostAction
     */
    function testGetAction() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new DomResourceDataResource();
        $pdr->setResourceId(1);
        $pdr->conn = $conn;

        // Create a non-admin user
        $nonAdminUserId = $this->createNonAdminUser();

        // Create some pages:
        // This one is public
        $publicResourceId = $this->createPage(true);
        // This one is not
        $unpublishedResourceId = $this->createPage(false);
        // This one is not published, but we will grant non-admin read access
        $grantedResourceId = $this->createPage(false);
        $sql = <<<SQL
        INSERT INTO resource_user_access
            (resource_id, user_id, can_edit)
        VALUES
            (:grantedResourceId, :nonAdminUserId, TRUE)
SQL;
        $sqlParams = array(
            'grantedResourceId' => $grantedResourceId
            ,'nonAdminUserId' => $nonAdminUserId
        );
        $stmt = $conn->prepare($sql);
        $stmt->execute($sqlParams);

        // For each resource, insert a record into ext_html_page
        $sql = <<<SQL
        INSERT INTO ext_html_page
            (resource_id, html_path, client_class)
        VALUES
             (:publicResourceId, 'path', 'Class')
            ,(:unpublishedResourceId, 'path2', 'Class2')
            ,(:grantedResourceId, 'path3', 'Class3')
SQL;
        $stmt = $conn->prepare($sql);
        $sqlParams = array(
             'publicResourceId' => $publicResourceId
            ,'unpublishedResourceId' => $unpublishedResourceId
            ,'grantedResourceId' => $grantedResourceId
        );
        $stmt->execute($sqlParams);

        // Now that we've created pages, query the database for its current state
        $sql = <<<SQL
        SELECT
             p.resource_id
            ,p.pathname
            ,p.redirect_on_error
            ,p.published
            ,CASE WHEN rua.resource_id IS NOT NULL THEN TRUE ELSE FALSE END AS user_has_access
            ,ehp.html_path
            ,ehp.client_class
        FROM resource p
        JOIN dom_resource ehp ON p.resource_id = ehp.resource_id
        LEFT JOIN resource_user_access rua ON p.resource_id = rua.resource_id AND rua.user_id = :userId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userId' => $nonAdminUserId]);
        $pagesState = $stmt->fetchAll();

        $sql = <<<SQL
        SELECT
             p.resource_id
            ,p.pathname
            ,p.redirect_on_error
            ,p.published
            ,ehp.html_path
            ,ehp.client_class
        FROM resource p
        JOIN dom_resource ehp ON p.resource_id = ehp.resource_id
        WHERE p.resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $publicResourceId]);
        $publicPageState = $stmt->fetchAll();


        // ACT
        // GET with admin login returns all pages and 200
        $this->setAdminAuth();
        $adminResult = $pdr->getAction();
        $adminStatus = $pdr->httpStatus;

        // GET with bogus ID parameter returns 404 and empty result
        $pdr->resourceData = array();
        $pdr->parameters['id'] = '69420';
        $bogusParamResult = $pdr->getAction();
        $bogusParamStatus = $pdr->httpStatus;

         // GET with valid ID parameter returns single record and 200, with data matching database.
        $this->setAdminAuth();
        $pdr->resourceData = array();
        $pdr->parameters['id'] = strval($publicResourceId);
        $singleParamResult = $pdr->getAction();
        $singleParamStatus = $pdr->httpStatus;
        $singleParamRecord = $singleParamResult[$publicResourceId];

        // Authenticated non-admin GET returns 404 on parameterized request for access-restricted resource
        $this->setNonAdminAuth();
        $pdr->resourceData = array();
        $pdr->parameters['id'] = strval($unpublishedResourceId);
        $unpublishedResult = $pdr->getAction();
        $unpublishedStatus = $pdr->httpStatus;

        // Authenticated non-admin, non-parameterized GET returns all and only pages avaliable to user
        $pdr->resourceData = array();
        $pdr->parameters = array();
        $noParamResult = $pdr->getAction();
        $noParamStatus = $pdr->httpStatus;

        // Non-authenticated GET returns 401 error.
        $this->unsetBasicAuth();
        $pdr->resourceData = array();
        $nonAuthResult = $pdr->getAction();
        $nonAuthStatus = $pdr->httpStatus;



        // ASSERT
        // GET with admin login returns all pages and 200

        foreach ($pagesState as $pageRecord)
            $this->assertArrayHasKey($pageRecord[0], $adminResult);

        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $adminStatus);

        // GET with bogus ID parameter returns 404 and empty result
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $bogusParamStatus);
        $this->assertEmpty($bogusParamResult);

        // GET with valid ID parameter returns single record and 200
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $singleParamStatus);
        $this->assertEquals(1, count($singleParamResult));

        // ... with data matching database.
        $this->assertSame(intval($publicPageState[0]['resource_id']), key($singleParamResult));
        $this->assertSame($publicPageState[0]['page_title'], $singleParamRecord['pageTitle']);
        $this->assertSame($publicPageState[0]['pathname'], $singleParamRecord['pathName']);
        $this->assertSame($publicPageState[0]['pagebuilder_class'], $singleParamRecord['pageBuilderClass']);
        $this->assertSame($publicPageState[0]['pageview_class'], $singleParamRecord['pageViewClass']);
        $this->assertSame(boolval($publicPageState[0]['redirect_on_error']), $singleParamRecord['redirectOnError']);
        $this->assertSame(boolval($publicPageState[0]['published']), $singleParamRecord['published']);
        $this->assertSame($publicPageState[0]['html_path'], $singleParamRecord['fileUrl']);
        $this->assertSame($publicPageState[0]['client_class'], $singleParamRecord['clientClass']);

        // Authenticated non-admin GET returns 404 on parameterized request for access-restricted resource
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $unpublishedStatus);
        $this->assertEmpty($unpublishedResult);

        // Authenticated non-admin, non-parameterized GET returns all and only pages avaliable to user
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $noParamStatus);

        foreach ($pagesState as $pageRecord) {
            $id = $pageRecord['resource_id'];
            $published = $pageRecord['published'];
            $userAccess = $pageRecord['user_has_access'];
            if ($published == true or $userAccess == true) {
                $this->assertArrayHasKey($id, $noParamResult);
            } else {
                $this->assertArrayNotHasKey($id, $noParamResult);
            }

        }

        // Non-authenticated GET returns 401 error.
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $nonAuthStatus);
        $this->assertEmpty($nonAuthResult);

    }

    /**
     * @depends testGetAction
     */
    function testDeleteAction() {
        // ARRANGE
        // Query the database for the file/resource created by testPostAction
        $conn = $this->getConnection();
        $sql = <<<SQL
        SELECT
             resource_id
            ,html_path
        FROM dom_resource
        WHERE client_class = 'LocalResourceClient'
        ORDER BY ext_html_page_id DESC
        LIMIT 1
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $resourceId = $result[0]['resource_id'];
        $dbHtmlPath = $result[0]['html_path'];

        // Instantiate an DomResourceDataResource and dependencies.
        $epr = new DomResourceDataResource();
        $epr->resourceId = $resourceId;
        $epr->conn = $conn;
        $epr->parameters['id'] = strval($resourceId);
        $client = new LocalResourceClient();
        $epr->client = $client;

        // ACT
        // Unauthenticated DELETE does not delete file
        $this->unsetBasicAuth();
        $epr->deleteAction();
        $fileExistsNoAuth = file_exists($dbHtmlPath);

        // Unauthenticated DELETE does not delete ext_html_page record nor resource record
        $sql = <<<SQL
        SELECT
             p.resource_id
            ,ext_html_page_id
        FROM resource p
        LEFT JOIN dom_resource ehp ON p.resource_id = ehp.resource_id
        WHERE p.resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
        $result = $stmt->fetchAll();
        $extHtmlResourceIdBefore = $result[0]['ext_html_page_id'];
        $resourceIdBefore = $result[0]['resource_id'];

        // Authenticated DELETE deletes file
        $this->setAdminAuth();
        $epr->deleteAction();
        $fileExistsAfter = file_exists($dbHtmlPath);
        // Authenticated DELETE deletes resource record
        $stmt->execute(['resourceId' => $resourceId]);
        $resultAfter = $stmt->fetchAll();

        // Authenticated DELETE returns 204
        $deletedStatus = $epr->httpStatus;


        // ASSERT
        // Unauthenticated DELETE does not delete file
        $this->assertTrue($fileExistsNoAuth);

        // Unauthenticated DELETE does not delete ext_html_page record nor resource record
        $this->assertNotEmpty($extHtmlResourceIdBefore);
        $this->assertNotEmpty($resourceIdBefore);

        // Authenticated DELETE deletes file
        $this->assertFalse($fileExistsAfter);

        // Authenticated DELETE deletes resource record
        $this->assertEmpty($resultAfter);

        // Authenticated DELETE returns 204
        $this->assertEquals(Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $deletedStatus);

        // Tear down the fixture.
        $this->destroyFileFixture();
        $this->destroyTestDatabase();

    }

}
