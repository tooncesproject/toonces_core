<?php
/**
 * @author paulanderson
 * PageDataResourceTest.php
 * Initial commit: Paul Anderson, 5/2/2018
 *
 * Unit tests for the PageApiResource class.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class PageDataResourceTest extends SqlDependentTestCase {

    function testValidatePathName() {
        // ARRANGE
        // Get connection and build a fixture
        $conn = $this->getConnection();
        $this->destroyTestDatabase();
        $this->buildTestDatabase();

        // Instantiate a ResourceDataResource and dependencies
        $pdr = new ResourceDataResource();
        $pdr->setResourceId(1);
        $pdr->conn = $conn;
        $pdr->resourceData = array();

        // Create a resource and a sub-resource
        $parentResourceId = $this->createPage(true, 1, 'parentpage');
        $childResourceId = $this->createPage(true, $parentResourceId, 'childpage');

        // ACT
        // pathname with disallowed characters invalidated
        $pdr->resourceData['pathName'] = '%&dank^$#';
        $invalidNameResult = $pdr->validatePathName($parentResourceId);

        // empty pathname invalidated
        $pdr->resourceData['pathName'] = '';
        $emptyResult = $pdr->validatePathName($parentResourceId);

        // Legit pathname validated
        $pdr->resourceData['pathName'] = 'good-path_name';
        $legitResult = $pdr->validatePathName($parentResourceId);

        // existing child path name invalidated
        $pdr->resourceData['pathName'] = 'childpage';
        $existingPathResult = $pdr->validatePathName($parentResourceId);

        // Existing child pathname with overwrite resource ID validated
        $pdr->resourceData['pathName'] = 'childpage';
        $existingOverwriteResult = $pdr->validatePathName($parentResourceId, $childResourceId);

        // Existing child pathname with bogus overwrite ID invalidated
        $pdr->resourceData['pathName'] = 'childpage';
        $badOverwriteResult = $pdr->validatePathName($parentResourceId, 9999);

        // Bogus parent resource ID invalidated
        $pdr->resourceData['pathName'] = 'good-path_name';
        $bogusParentResult = $pdr->validatePathName(69420);



        // ASSERT
        // pathname with invalid characters invalidated
        $this->assertFalse($invalidNameResult);

        // empty pathname invalidated
        $this->assertFalse($emptyResult);

        // Legit pathname validated
        $this->assertTrue($legitResult);

        // existing child path name invalidated
        $this->assertFalse($existingPathResult);

        // Existing child pathname with overwrite resource ID validated
        $this->assertTrue($existingOverwriteResult);

        // Existing child pathname with bogus overwrite ID invalidated
        $this->assertFalse($badOverwriteResult);

        // Bogus parent resource ID invalidated
        $this->assertFalse($bogusParentResult);

    }

    /**
     * @depends testValidatePathName
     */
    function testGeneratePathName() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->conn = $conn;
        $pdr->setResourceId(1);
        $pdr->resourceData = array('pageTitle' => 'Hi! I\'m a resource.');

        // ACT
        $newPathName = $pdr->generatePathName();

        // ASSERT
        // pathName is set
        $this->assertSame($newPathName, $pdr->resourceData['pathName']);
        // pathName is valid
        $this->assertTrue($pdr->validatePathName(1));

    }


    function testValidateResourceClass() {

        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $pdr = new ResourceDataResource();
        $pdr->setResourceId(1);
        $pdr->resourceData = array();

        // ACT
        // bogus resource class invalidated
        $pdr->resourceData['resourceClass'] = 'foo';
        $bogusResult = $pdr->validateResourceClass();

        // legit resource class validated
        $pdr->resourceData['resourceClass'] = 'Resource';
        $goodResult = $pdr->validateResourceClass();

        // ASSERT
        $this->assertFalse($bogusResult);
        $this->assertTrue($goodResult);

    }


    /**
     * @depends testValidatePathName
     */
    function testRecursiveCheckWriteAccess() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->conn = $conn;
        $pdr->setResourceId(1);

        // Create users.
        $nonAdminUserId = $this->createNonAdminUser();
        $adminUserId = 1;

        // Create a hierarchy of pages.
        $parentResourceId = $this->createPage(true);
        $childPageOneId = $this->createPage(true, $parentResourceId);
        $childPageTwoId = $this->createPage(true, $parentResourceId);
        $grandchildPageOneId = $this->createPage(true, $childPageOneId);
        $grandchildPageTwoId = $this->createPage(true, $childPageOneId);
        $grandchildPageThreeId = $this->createPage(true, $childPageTwoId);

        // Grant write access to some of the pages.
        // (not going to worry about read-only access;
        // That is covered by TestCheckPageUserAccess).
        $sql = <<<SQL
        INSERT INTO resource_user_access
            (resource_id, user_id, can_edit)
        VALUES
             (:childPageOneId, :userId, 1)
            ,(:grandchildPageOneId, :userId, 1)
            ,(:childPageTwoId, :userId, 1)
            ,(:grandchildPageThreeId, :userId, 1)
SQL;
        $sqlParams = array(
             'childPageOneId' => $childPageOneId
            ,'grandchildPageOneId' => $grandchildPageOneId
            ,'childPageTwoId' => $childPageTwoId
            ,'grandchildPageThreeId' => $grandchildPageThreeId
            ,'userId' => $nonAdminUserId
        );
        $stmt = $conn->prepare($sql);
        $stmt->execute($sqlParams);


        // ACT
        // Access denied where access not granted, no children.
        $noChildrenNoAccessResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $grandchildPageTwoId);

        // Access denied where access not granted to base, resource has children with access
        $childrenNoAccessResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $parentResourceId);

        // Access denied where access granted to base, resource has a child with no access
        $grantedBaseResult =  $pdr->recursiveCheckWriteAccess($nonAdminUserId, $childPageOneId);

        // Access allowed where access granted to base, base has no children
        $grantedNoChildrenResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $grandchildPageThreeId);

        // Access allowed where access granted to base and any of its children
        $allGrantedResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $childPageTwoId);

        // Access allowed where user is admin
        $adminUserResult = $pdr->recursiveCheckWriteAccess($adminUserId, $parentResourceId);


        // ASSERT
        // Access denied where access not granted, no children.
        $this->assertFalse($noChildrenNoAccessResult);

        // Access denied where access not granted to base, resource has children with access
        $this->assertFalse($childrenNoAccessResult);

        // Access allowed where access granted to base, base has no children
        $this->assertTrue($grantedNoChildrenResult);

        // Access allowed where access granted to base and any of its children
        $this->assertTrue($allGrantedResult);

        // Access allowed where user is admin
        $this->assertTrue($adminUserResult);

    }

    /**
     * @depends testValidatePathName
     *
     */
    function testPostAction() {
        // ARRANGE
        // get SQL connection
        $conn = $this->getConnection();

        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->setResourceId(1);
        $pdr->conn = $conn;

        // Create an unpublished resource
        $unpublishedResourceId = $this->createPage(false);

        // create some input data mockups
        // Invalid post - missing resource title
        $invalidPost = array (
             'ancestorResourceId' => 1
        );

        // Valid post - has resource class
        $validPost = $invalidPost;
        $validPost['resourceClass'] = 'Resource';

        // Valid post but no non-admin access
        $validNoAccessPost = $validPost;
        $validNoAccessPost['ancestorResourceId'] = $unpublishedResourceId;

        // Invalid post - bogus ancestorResourceId
        $badResourceIdPost = $validPost;
        $badResourceIdPost['ancestorResourceId'] = 69420;

        // Invalid post - Invalid pathname
        $badPathnamePost = $validPost;
        $badPathnamePost['pathName'] = 'donkey%#$%';

        // Invalid post - bogus Resource class
        $badResourcePost = $validPost;
        $badResourcePost['resourceClass'] = 'foo';

        // Record how many pages currently reside in the database
        $sql = "SELECT COUNT(*) FROM resource";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountBefore = $result[0][0];


        // ACT
        // POST with failed authentication returns 401 error
        $this->unsetBasicAuth();
        $pdr->resourceData = $validPost;
        $pdr->postAction();
        $failedAuthStatus = $pdr->httpStatus;

        // POST with parent resource to which non-admin user doesn't have access returns 404
        $this->setNonAdminAuth();
        $pdr->resourceData = $validNoAccessPost;
        $pdr->postAction();
        $nonAdminStatus = $pdr->httpStatus;

        // POST with invalid or missing data returns 400 error
        $this->setAdminAuth();
        $pdr->resourceData = $invalidPost;
        $pdr->postAction();
        $invalidPostStatus = $pdr->httpStatus;

        // POST with non-existent parent resource ID returns 400
        $pdr->resourceData = $badResourceIdPost;
        $pdr->postAction();
        $badPidStatus = $pdr->httpStatus;


        // POST with invalid pathName returns 400 error
        $pdr->resourceData = $badPathnamePost;
        $pdr->postAction();
        $badPathnameStatus = $pdr->httpStatus;

        // POST with invalid Resource class returns 400 error
        $pdr->resourceData = $badResourcePost;
        $pdr->postAction();
        $badPbStatus = $pdr->httpStatus;

        // Page not created after unauthenticated or invalid attempts.
        $sql = "SELECT COUNT(*) FROM resource";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountAfter = $result[0][0];

        // POST with valid input returns 201 status
        $pdr->resourceData = $validPost;
        $validResult = $pdr->postAction();
        $goodStatus = $pdr->httpStatus;

        // POST with valid input created a record in the database.
        $resourceIdStr = key($validResult);
        $newResourceId = intval($resourceIdStr);
        $sql = <<<SQL
        SELECT
             pathname
            ,resource_class
            ,redirect_on_error
            ,published
        FROM resource
        WHERE resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $newResourceId]);
        $result = $stmt->fetchAll();
        $insertedPathName = $result[0]['pathname'];
        $insertedResourceClass = $result[0]['resource_class'];
        $insertedRedirectOnError = boolval($result[0]['redirect_on_error']);
        $insertedPublished = boolval($result[0]['published']);


        // ASSERT
        // POST with failed authentication returns 401 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $failedAuthStatus);

        // POST with parent resource to which non-admin user doesn't have access returns 404
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);

        // POST with invalid or missing data returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidPostStatus);

        // POST with non-existent parent resource ID returns 400
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $badPidStatus);

        // POST with invalid pathName returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPathnameStatus);

        // POST with invalid Resource class returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPbStatus);

        // Page not created after unauthenticated or invalid attempts.
        $this->assertEquals($pageCountBefore, $pageCountAfter);

        // POST with valid input returns 201 status
        $this->assertEquals(Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse'), $goodStatus);

        // POST with valid input created a record in the database.

        $this->assertSame($validResult[$newResourceId]['pathName'], $insertedPathName);
        $this->assertSame($validResult[$newResourceId]['resourceClass'], $insertedResourceClass);
        $this->assertSame($validResult[$newResourceId]['redirectOnError'], $insertedRedirectOnError);
        $this->assertSame($validResult[$newResourceId]['published'], $insertedPublished);

    }


    /**
     * @depends testValidatePathName
     */
    function testPutAction() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->conn = $conn;
        $pdr->setResourceId(1);

        // Create an unpublished resource
        $unpublishedResourceId = $this->createPage(false);

        // create some input data mockups
        // valid post
        $validPost = array (
             'pathName' => 'newpathname'
            ,'resourceClass' => 'Resource'
            ,'redirectOnError' => true
            ,'published' => true
        );

        // invalid post
        $invalidPost = $validPost;
        $invalidPost['published'] = 'poop';

        // invalid pathname
        $invalidPathnamePost = $validPost;
        $invalidPathnamePost['pathName'] = '$%$^#&';

        // Query database for state before PUT attempts
        $sql = <<<SQL
            SELECT
                 pathname
                ,resource_class
                ,redirect_on_error
                ,published
            FROM resource
            WHERE resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $unpublishedResourceId]);
        $result = $stmt->fetchAll();
        $pageStateBefore = $result[0];


        // ACT
        // PUT with failed authentication returns 401 error
        $this->unsetBasicAuth();
        $pdr->parameters['id'] = strval($unpublishedResourceId);
        $pdr->resourceData = $validPost;
        $pdr->putAction();
        $badAuthStatus = $pdr->httpStatus;

        // PUT without valid ID parameter returns 405 error
        $pdr->parameters = array();
        $this->setAdminAuth();
        $pdr->putAction();
        $badIdStatus = $pdr->httpStatus;

        // PUT with failed data validation returns 400 error
        $pdr->parameters['id'] = strval($unpublishedResourceId);
        $pdr->resourceData = $invalidPost;
        $pdr->putAction();
        $invalidDataStatus = $pdr->httpStatus;

        // PUT where authenticated user doesn't have write access returns 404 error
        $this->setNonAdminAuth();
        $pdr->parameters['id'] = strval($unpublishedResourceId);
        $pdr->resourceData = $validPost;
        $pdr->putAction();
        $nonAdminStatus = $pdr->httpStatus;

        // PUT with invalid pathName returns 400 error
        $this->setAdminAuth();
        $pdr->resourceData = $invalidPathnamePost;
        $pdr->putAction();
        $invalidPathnameStatus = $pdr->httpStatus;

        // No change to resource data after unauthenticated or invalid PUT attempts
        // Query database for state after PUT attempts
        $sql = <<<SQL
            SELECT
                 pathname
                ,resource_class
                ,redirect_on_error
                ,published
            FROM resource
            WHERE resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $unpublishedResourceId]);
        $result = $stmt->fetchAll();
        $pageStateAfter = $result[0];

        // PUT with valid input returns 200 status
        $this->setAdminAuth();
        $pdr->resourceData = $validPost;
        $pdr->parameters['id'] = strval($unpublishedResourceId);
        $validResult = $pdr->putAction();
        $validPutStatus = $pdr->httpStatus;

        // Page record is updated in database
        $sql = <<<SQL
            SELECT
                 pathname
                ,resource_class
                ,redirect_on_error
                ,published
            FROM resource
            WHERE resource_id = :resourceId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $unpublishedResourceId]);
        $result = $stmt->fetchAll();
        $insertedPathName = $result[0]['pathname'];
        $insertedResourceClass = $result[0]['resource_class'];
        $insertedRedirectOnError = boolval($result[0]['redirect_on_error']);
        $insertedPublished = boolval($result[0]['published']);


        // ASSERT
        // PUT with failed authentication returns 401 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $badAuthStatus);

        // PUT without valid ID parameter returns 405 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $badIdStatus);

        // PUT with failed data validation returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidDataStatus);

        // PUT where authenticated user doesn't have write access returns 404 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);

        // PUT with invalid pathName returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidPathnameStatus);

        // No change to resource data after unauthenticated or invalid PUT attempts
        $this->assertSame($pageStateBefore, $pageStateAfter);

        // PUT with valid input returns 200 status
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $validPutStatus);

        // Page record is updated in database
        $this->assertSame($validResult[$unpublishedResourceId]['pathName'], $insertedPathName);
        $this->assertSame($validResult[$unpublishedResourceId]['resourceClass'], $insertedResourceClass);
        $this->assertSame($validResult[$unpublishedResourceId]['redirectOnError'], $insertedRedirectOnError);
        $this->assertSame($validResult[$unpublishedResourceId]['published'], $insertedPublished);

    }


    /**
     * @depends testValidatePathName
     */
    function testGetAction() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->conn = $conn;
        $pdr->resourceId = 1;

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

        // Now that we've created pages, query the database for its current state
        $sql = <<<SQL
        SELECT
             r.resource_id
            ,r.pathname
            ,r.resource_class
            ,r.redirect_on_error
            ,r.published
            ,CASE WHEN rua.resource_id IS NOT NULL THEN TRUE ELSE FALSE END AS user_has_access
        FROM resource r
        LEFT JOIN resource_user_access rua ON r.resource_id = rua.resource_id AND rua.user_id = :userId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userId' => $nonAdminUserId]);
        $pagesState = $stmt->fetchAll();

        $sql = <<<SQL
        SELECT
             r.resource_id
            ,r.pathname
            ,r.resource_class
            ,r.redirect_on_error
            ,r.published
        FROM resource r
        WHERE resource_id = :resourceId
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

        // Authenticated non-admin, non-parameterized GET returns all and only pages available to user
        $pdr->resourceData = array();
        $pdr->parameters = array();
        $noParamResult = $pdr->getAction();
        $noParamStatus = $pdr->httpStatus;

        // Non-authenticated GET returns all and only published pages
        $this->unsetBasicAuth();
        $pdr->resourceData = array();
        $nonAuthResult = $pdr->getAction();
        $nonAuthStatus = $pdr->httpStatus;

        // Non-authenticated GET on unpublished resource parameter returns 404
        $this->unsetBasicAuth();
        $pdr->resourceData = array();
        $pdr->parameters['id'] = strval($grantedResourceId);
        $noAuthUnpublishedResult = $pdr->getAction();
        $noAuthUnpublishedStatus = $pdr->httpStatus;


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
        $this->assertSame($publicPageState[0]['pathname'], $singleParamRecord['pathName']);
        $this->assertSame($publicPageState[0]['resource_class'], $singleParamRecord['resourceClass']);
        $this->assertSame(boolval($publicPageState[0]['redirect_on_error']), $singleParamRecord['redirectOnError']);
        $this->assertSame(boolval($publicPageState[0]['published']), $singleParamRecord['published']);

        // Authenticated non-admin GET returns 404 on parameterized request for access-restricted resource
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $unpublishedStatus);
        $this->assertEmpty($unpublishedResult);

        // Authenticated non-admin, non-parameterized GET returns all and only pages available to user
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

        // Non-authenticated GET returns all and only published pages
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $nonAuthStatus);
        foreach ($pagesState as $pageRecord) {
            $id = $pageRecord['resource_id'];
            $published = $pageRecord['published'];
            if ($published == true) {
                $this->assertArrayHasKey($id, $nonAuthResult);
            } else {
                $this->assertArrayNotHasKey($id, $nonAuthResult);
            }
        }


        // Non-authenticated GET on unpublished resource parameter returns 404
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAuthUnpublishedStatus);
        $this->assertEmpty($noAuthUnpublishedResult);

    }

    /**
     * @depends testValidatePathName
     */
    function testDeleteAction() {
        // ARRANGE
        // Instantiate a ResourceDataResource and dependencies
        $conn = $this->getConnection();
        $pdr = new ResourceDataResource();
        $pdr->conn = $conn;
        $pdr->setResourceId(1);

        // Create users.
        $nonAdminUserId = $this->createNonAdminUser();
        $adminUserId = 1;

        // Create a hierarchy of pages.
        $parentResourceId = $this->createPage(true);
        $childPageOneId = $this->createPage(true, $parentResourceId);
        $childPageTwoId = $this->createPage(true, $parentResourceId);
        $grandchildPageOneId = $this->createPage(true, $childPageOneId);
        $grandchildPageTwoId = $this->createPage(true, $childPageOneId);
        $grandchildPageThreeId = $this->createPage(true, $childPageTwoId);

        // Grant write access to some of the pages.
        // (not going to worry about read-only access;
        // That is covered by TestCheckPageUserAccess).
        $sql = <<<SQL
        INSERT INTO resource_user_access
            (resource_id, user_id, can_edit)
        VALUES
             (:childPageOneId, :userId, 1)
            ,(:grandchildPageOneId, :userId, 1)
            ,(:childPageTwoId, :userId, 1)
            ,(:grandchildPageThreeId, :userId, 1)
SQL;
        $sqlParams = array(
             'childPageOneId' => $childPageOneId
            ,'grandchildPageOneId' => $grandchildPageOneId
            ,'childPageTwoId' => $childPageTwoId
            ,'grandchildPageThreeId' => $grandchildPageThreeId
            ,'userId' => $nonAdminUserId
        );
        $stmt = $conn->prepare($sql);
        $stmt->execute($sqlParams);

        // Capture the count of pages before any operations
        $sql = "SELECT COUNT(*) FROM resource";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountBefore = $result[0][0];


        // ACT
        // Unauthenticated attempt returns 401
        $this->unsetBasicAuth();
        $pdr->parameters['id'] = strval($grandchildPageThreeId);
        $pdr->deleteAction();
        $unauthenticatedStatus = $pdr->httpStatus;

        // Authenticated attempt without parameter returns 405
        $this->setAdminAuth();
        $pdr->parameters = array();
        $pdr->deleteAction();
        $noParameterStatus = $pdr->httpStatus;

        // Authenticated attempt where user has no write access to an affected resource returns 401
        $this->setNonAdminAuth();
        $pdr->parameters['id'] = strval($childPageOneId);
        $pdr->deleteAction();
        $noAccessStatus = $pdr->httpStatus;

        // Invalid attempts so far have not deleted any pages
        $sql = "SELECT COUNT(*) FROM resource";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountAfter = $result[0][0];

        // Authenticated non-admin user successfully deletes resource with write access, returns 204
        $pdr->parameters['id'] = strval($grandchildPageThreeId);
        $pdr->deleteAction();
        $nonAdminValidStatus = $pdr->httpStatus;
        $sql = "SELECT resource_id FROM resource WHERE resource_id = :resourceId";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $grandchildPageThreeId]);
        $resultAfterDelete = $stmt->fetchAll();

        // Authenticated admin user successfully deletes resource and all its children, returns 204
        $this->setAdminAuth();
        $pdr->parameters['id'] = strval($parentResourceId);
        $pdr->deleteAction();
        $adminDeleteStatus = $pdr->httpStatus;
        // ... query for the current count of pages
        $sql = "SELECT COUNT(*) FROM resource";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountFinal = $result[0][0];


        // ASSERT
        // Unauthenticated attempt returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $unauthenticatedStatus);

        // Authenticated attempt without parameter returns 405
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $noParameterStatus);

        // Authenticated attempt where user has no write access to an affected resource returns 401
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $noAccessStatus);

        // Invalid attempts so far have not deleted any pages
        $this->assertEquals($pageCountBefore, $pageCountAfter);

        // Authenticated non-admin user successfully deletes resource with write access, returns 204
        $this->assertEquals(Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $nonAdminValidStatus);
        $this->assertEmpty($resultAfterDelete);

        // Authenticated admin user successfully deletes resource and all its children, returns 204
        $this->assertEquals(Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $adminDeleteStatus);
        // ... All 6 pages created by the unit test should be deleted.
        $this->assertEquals($pageCountBefore - 6, $pageCountFinal);

        // Test case completed; tear down fixture.
        $this->destroyTestDatabase();

    }

}
