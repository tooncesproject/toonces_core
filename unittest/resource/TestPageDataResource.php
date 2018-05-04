<?php
/**
 * @author paulanderson
 * TestPageDataResource.php
 * Initial commit: Paul Anderson, 5/2/2018
 * 
 * Unit tests for the PageApiResource class.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestPageDataResource extends SqlDependentTestCase {
    
    function testValidatePathName() {
        // ARRANGE
        // Get connection and build a fixture
        $conn = $this->getConnection();
        $this->buildTestDatabase();

        // Instantiate a PageDataResource and dependencies
        $pageView = new JsonPageView(1);
        $pageView->setSQLConn($conn);
        $pdr = new PageDataResource($pageView);
        $pdr->resourceData = array();
        
        // Create a page and a sub-page
        $parentPageId = $this->createPage(true, 1, 'parentpage');
        $childPageId = $this->createPage(true, $parentPageId, 'childpage');

        // ACT
        // pathname with disallowed characters invalidated
        $pdr->resourceData['pathName'] = '%&dank^$#';
        $invalidNameResult = $pdr->validatePathName($parentPageId);
        
        // empty pathname invalidated
        $pdr->resourceData['pathName'] = '';
        $emptyResult = $pdr->validatePathName($parentPageId);
        
        // Legit pathname validated
        $pdr->resourceData['pathName'] = 'good-path_name';
        $legitResult = $pdr->validatePathName($parentPageId);

        // existing child path name invalidated
        $pdr->resourceData['pathName'] = 'childpage';
        $existingPathResult = $pdr->validatePathName($parentPageId);

        // Existing child pathname with overwrite page ID validated
        $pdr->resourceData['pathName'] = 'childpage';
        $existingOverwriteResult = $pdr->validatePathName($parentPageId, $childPageId);
        
        // Existing child pathname with bogus overwrite ID invalidated
        $pdr->resourceData['pathName'] = 'childpage';
        $badOverwriteResult = $pdr->validatePathName($parentPageId, 9999);
        
        // Bogus parent page ID invalidated
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
        
        // Existing child pathname with overwrite page ID validated
        $this->assertTrue($existingOverwriteResult);
        
        // Existing child pathname with bogus overwrite ID invalidated
        $this->assertFalse($badOverwriteResult);
        
        // Bogus parent page ID invalidated
        $this->assertFalse($bogusParentResult);
        
    }
    
    /**
     * @depends testValidatePathName
     */
    function testGeneratePathName() {
        // ARRANGE
        // Instantiate a PageDataResource and dependencies
        $pageView = new JsonPageView(1);
        $pageView->setSQLConn($conn);
        $pdr = new PageDataResource($pageView);
        $pdr->resourceData = array('pageTitle' => 'Hi! I\'m a page.');

        // ACT
        $newPathName = $pdr->generatePathName();

        // ASSERT
        // pathName is set
        $this->assertSame($newPathName, $pdr->resourceData['pathName']);
        // pathName is valid
        $this->assertTrue($pdr->validatePathName(1));

    }
    
    
    function testValidatePageBuilderClass() {
        // ARRANGE
        // Instantiate a PageDataResource and dependencies
        $pageView = new JsonPageView(1);
        $pdr = new PageDataResource($pageView);
        $pdr->resourceData = array();

        // ACT
        // bogus pagebuilder class invalidated
        $pdr->resourceData['pageBuilderClass'] = 'foo';
        $bogusResult = $pdr->validatePageBuilderClass();
        
        // legit pagebuilder class validated
        $pdr->resourceData['pageBuilderClass'] = 'Toonces404PageBuilder';
        $goodResult = $pdr->validatePageBuilderClass();
        
        // ASSERT
        $this->assertFalse($bogusResult);
        $this->assertTrue($goodResult);
    }
    
    
    function testValidatePageViewClass() {
        // ARRANGE
        // Instantiate a PageDataResource and dependencies
        $pageView = new JsonPageView(1);
        $pdr = new PageDataResource($pageView);
        $pdr->resourceData = array();
        
        // ACT
        // bogus pagebuilder class invalidated
        $pdr->resourceData['pageViewClass'] = 'foo';
        $bogusResult = $pdr->validatePageViewClass();
        
        // legit pagebuilder class validated
        $pdr->resourceData['pageViewClass'] = 'JsonPageView';
        $goodResult = $pdr->validatePageViewClass();
        
        // ASSERT
        $this->assertFalse($bogusResult);
        $this->assertTrue($goodResult);
    }
    
    /**
     * @depends testValidatePathName
     */
    function testRecursiveCheckWriteAccess() {
        // ARRANGE
        // Instantiate a PageDataResource and dependencies
        $conn = $this->getConnection();
        $pageView = new JsonPageView(1);
        $pageView->setSQLConn($conn);
        $pdr = new PageDataResource($pageView);
        
        // Create users.
        $nonAdminUserId = $this->createNonAdminUser();
        $adminUserId = $this->createAdminUser();
        
        // Create a hierarchy of pages.
        $parentPageId = $this->createPage(true);
        $childPageOneId = $this->createPage(true, $parentPageId);
        $childPageTwoId = $this->createPage(true, $parentPageId);
        $grandchildPageOneId = createPage(true, $childPageOneId);
        $grandchildPageTwoId = createPage(true, $childPageOneId);
        $grandchildPageThreeId = createPage(true, $childPageTwoId);
        
        // Grant write access to some of the pages.
        // (not going to worry about read-only access; 
        // That is covered by TestCheckPageUserAccess).
        $sql = <<<SQL
        INSERT INTO page_user_access
            (page_id, user_id, can_edit)
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
        
        // Access denied where access not granted to base, page has children with access 
        $childrenNoAccessResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $parentPageId);
        
        // Access denied where access granted to base, page has a child with no access
        $grantedBaseResult =  $pdr->recursiveCheckWriteAccess($nonAdminUserId, $childPageOneId);
        
        // Access allowed where access granted to base, base has no children
        $grantedNoChildrenResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $grandchildPageTwoId);
        
        // Access allowed where access granted to base and any of its children
        $allGrantedResult = $pdr->recursiveCheckWriteAccess($nonAdminUserId, $childPageTwoId);
        
        // Access allowed where user is admin
        $adminUserResult = $pdr->recursiveCheckWriteAccess($adminUserId, $parentPageId);
        
        
        // ASSERT
        // Access denied where access not granted, no children.
        $this->assertFalse($noChildrenNoAccessResult);
        
        // Access denied where access not granted to base, page has children with access
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
     */
    function testPostAction() {
        // ARRANGE
        // get SQL connection
        $conn = $this->getConnection();

        // Instantiate a PageDataResource and dependencies
        $conn = $this->getConnection();
        $pageView = new JsonPageView(1);
        $pageView->setSQLConn($conn);
        $pdr = new PageDataResource($pageView);
        
        // Create an unpublished page
        $unpublishedPageId = $this->createPage(false);
        
        // create some input data mockups
        // Invalid post - missing page title
        $invalidPost = array (
             'ancestorPageId' => 1
            ,'pageBuilderClass' => 'Toonces404PageBuilder'
            ,'pageViewClass' => 'JsonPageView'
        );
        
        // Valid post - has page title
        $validPost = $invalidPost;
        $validPost['pageTitle'] = 'Page Title';
        
        // Valid post but no non-admin access
        $validNoAccessPost = $validPost;
        $validNoAccessPost['ancestorPageId'] = $unpublishedPageId;
        
        // Invalid post - bogus ancestorPageId
        $badPageIDPost = $validPost;
        $badPageIdPost['ancestorPageId'] = 69420;
        
        // Invalid post - Invalid pathname
        $badPathnamePost = $validPost;
        $badPathnamePost['pathName'] = 'donkey%#$%';
        
        // Invalid post - bogus pagebuilder class
        $badPbPost = $validPost;
        $badPbPost['pageBuilderClass'] = 'foo';
        
        // Invalid post - bogus pageview class
        $badPvPost = $validPost;
        $badPvPost['pageViewClass'] = 'foo';
        
        // invalid post - Bogus page type ID
        $badPtPost = $validPost;
        $badPtPost['pageTypeId'] = 69;
        
        // Record how many pages currently reside in the database
        $sql = "SELECT COUNT(*) FROM pages";
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
        
        // POST with parent page to which non-admin user doesn't have access returns 404
        $this->setNonAdminAuth();
        $pdr->resourceData = $validNoAccessPost;
        $pdr->postAction();
        $nonAdminStatus = $pdr->httpStatus;
        
        // POST with invalid or missing data returns 400 error
        $this->setAdminAuth();
        $pdr->resourceData = $validPost;
        $pdr->postAction;
        $invalidPostStatus = $pdr->httpStatus;

        // POST with non-existent parent page ID returns 400
        $pdr->resourceData = $badPageIdPost;
        $pdr->postAction();
        $badPidStatus = $pdr->httpStatus;
        
        // POST with invalid pathName returns 400 error
        $pdr->resourceData = $badPathnamePost;
        $pdr->postAction();
        $badPathnameStatus = $pdr->httpStatus;
        
        // POST with invalid pagebuilder class returns 400 error
        $pdr->resourceData = $badPbPost;
        $pdr->postAction();
        $badPbStatus = $pdr->httpStatus;
        
        // POST with invalid pageview class returns 400 error
        $pdr->resourceData = $badPvPost;
        $pdr->postAction();
        $badPvStatus = $pdr->httpStatus;

        // POST with invalid page type ID return 400 error
        $pdr->resourceData = $badPtPost;
        $pdr->postAction();
        $badPtStatus = $pdr->httpStatus;
        
        // Page not created after unauthenticated or invalid attempts.
        $sql = "SELECT COUNT(*) FROM pages";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageCountAfter = $result[0][0];
        
        // POST with valid input returns 201 status
        $pdr->resourceData = $validPost;
        $validResult = $pdr->postAction();
        $goodStatus = $pdr->httpStatus;
        
        // POST with valid input created a record in the database.
        $pageIdStr = key($validResult);
        $newPageId = intval($pageIdStr);
        $sql = <<<SQL
        SELECT
             page_title
            ,pathname
            ,pagebuilder_class
            ,pageview_class
            ,redirect_on_error,
            ,published
            ,pagetype_id
        FROM pages
        WHERE page_id = :pageId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $newPageId]);
        $result = $stmt->fetchAll;
        $insertedPageTitle = $result[0][0];
        $insertedPathName = $result[0][1];
        $insertedPageBuilderClass = $result[0][2];
        $insertedPageViewClass = $result[0][3];
        $insertedRedirectOnError = $result[0][4];
        $insertedPublished = $result[0][5];
        $insertedPageTypeId = $result[0][6];

        
        // ASSERT
        // POST with failed authentication returns 401 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $failedAuthStatus);
        
        // POST with parent page to which non-admin user doesn't have access returns 404
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);

        // POST with invalid or missing data returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidPostStatus);
        
        // POST with non-existent parent page ID returns 400
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPidStatus);
        
        // POST with invalid pathName returns 400 error 
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPathnameStatus);

        // POST with invalid pagebuilder class returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPbStatus);

        // POST with invalid pageview class returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPvStatus);
        
        // POST with invalid page type ID return 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badPtStatus);
        
        // Page not created after unauthenticated or invalid attempts.
        $this->assertEquals($pageCountBefore, $pageCountAfter);
        
        // POST with valid input returns 201 status
        $this->assertEquals(Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse'), $goodStatus);
        
        // POST with valid input created a record in the database.
        $this->assertSame($validResult['pageTitle'], $insertedPageTitle);
        $this->assertSame($validResult['pathName'], $insertedPathName);
        $this->assertSame($validResult['pageBuilderClass'], $insertedPageBuilderClass);
        $this->assertSame($validResult['pageViewClass'], $insertedPageViewClass);
        $this->assertSame($validResult['redirectOnError'], $insertedRedirectOnError);
        $this->assertSame($validResult['published'], $insertedPublished);
        $this->assertSame($validResult['pageTypeId'], $insertedPageTypeId);
        
    }
   
    
    /**
     * @depends testValidatePathName
     */
    function testPutAction() {
        // ARRANGE
        // get SQL connection
        $conn = $this->getConnection();
        
        // Instantiate a PageDataResource and dependencies
        $conn = $this->getConnection();
        $pageView = new JsonPageView(1);
        $pageView->setSQLConn($conn);
        $pdr = new PageDataResource($pageView);
        
        // Create an unpublished page
        $unpublishedPageId = $this->createPage(false);
        
        // create some input data mockups
        // valid post
        $validPost = array (
             'pageTitle' => 'New Title'
            ,'pathName' => 'newpathname'
            ,'pageViewClass' => 'FilePageView'
            ,'pageBuilderClass' => 'DocumentEndpointPageBuilder'
            ,'redirectOnError' => true
            ,'published' => true
            ,'pageTypeId' => true
        );
        
        // invalid post
        $invalidPost = $validPost;
        $invalidPost['published'] = 'poop';
        
        // invalid pathname
        $invalidPathnamePost = $validPost;
        $invalidPathnamePost['pathName'] = '$%$^#&';
        
        // invalid pageType
        $invalidPageTypePost = $validPost;
        $invalidPageTypePost['pageTypeId'] = 53456;
        
        // Query database for state before PUT attempts
        $sql = <<<SQL
            SELECT
                 page_title
                ,pathname
                ,pageview_class
                ,pagebuilder_class
                ,redirect_on_error
                ,published
                ,pagetype_id
            FROM pages
            WHERE page_id = :pageId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $unpublishedPageId]);
        $result = $stmt->fetchAll();
        $pageStateBefore = $result[0];
        
        // ACT
        // PUT with failed authentication returns 401 error
        $this->unsetBasicAuth();
        $pdr->parameters['id'] = strval($unpublishedPageId);
        $pdr->resourceData = $validPost;
        $pdr->putAction();
        $badAuthStatus = $pdr->httpStatus;
        
        // PUT without valid ID parameter returns 404 error
        $pdr->parameters = array();
        $this->setAdminAuth();
        $pdr->putAction();
        $badIdStatus = $pdr->httpStatus;
        
        // PUT with failed data validation returns 400 error
        $pdr->parameters['id'] = strval($unpublishedPageId);
        $pdr->resourceData = $invalidPost;
        $pdr->putAction();
        $invalidDataStatus = $pdr->httpStatus;
        
        // PUT where authenticated user doesn't have write access returns 404 error
        $this->setNonAdminAuth();
        $pdr->resourceData = $validPost;
        $pdr->putAction();
        $nonAdminStatus = $pdr->httpStatus;
        
        // PUT with invalid pathName returns 400 error
        $this->setAdminAuth();
        $pdr->resourceData = $invalidPathnamePost;
        $pdr->putAction();
        $invalidPathnameStatus = $pdr->httpStatus;        
        
        // PUT with invalid pageTypeId returns 400 error
        $pdr->resourceData = $invalidPageTypePost;
        $pdr->putAction();
        $invalidPtStatus = $pdr->httpStatus;

        // No change to page data after unauthenticated or invalid PUT attempts
        // Query database for state after PUT attempts
        $sql = <<<SQL
            SELECT
                 page_title
                ,pathname
                ,pageview_class
                ,pagebuilder_class
                ,redirect_on_error
                ,published
                ,pagetype_id
            FROM pages
            WHERE page_id = :pageId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $unpublishedPageId]);
        $result = $stmt->fetchAll();
        $pageStateAfter = $result[0];
        
        // PUT with valid input returns 200 status
        $pdr->resourceData = $validPost;
        $validResult = $pdr->putAction();
        $validPutStatus = $pdr->httpStatus;
        
        // Page record is updated in database
        $sql = <<<SQL
            SELECT
                 page_title
                ,pathname
                ,pageview_class
                ,pagebuilder_class
                ,redirect_on_error
                ,published
                ,pagetype_id
            FROM pages
            WHERE page_id = :pageId
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $unpublishedPageId]);
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $newPageId]);
        $result = $stmt->fetchAll;
        $insertedPageTitle = $result[0][0];
        $insertedPathName = $result[0][1];
        $insertedPageViewClass = $result[0][3];
        $insertedPageBuilderClass = $result[0][2];
        $insertedRedirectOnError = $result[0][4];
        $insertedPublished = $result[0][5];
        $insertedPageTypeId = $result[0][6];
        
        
        // ASSERT
        // PUT with failed authentication returns 401 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $badAuthStatus);

        // PUT without valid ID parameter returns 404 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $badIdStatus);
        
        // PUT with failed data validation returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidDataStatus);
        
        // PUT where authenticated user doesn't have write access returns 404 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);
        
        // PUT with invalid pathName returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidPathnameStatus);
        
        // PUT with invalid pageTypeId returns 400 error
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidPtStatus);
        
        // No change to page data after unauthenticated or invalid PUT attempts
        $this->assertSame($pageStateBefore, $pageStateAfter);
        
        // PUT with valid input returns 200 status
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $validPutStatus);

        // Page record is updated in database
        $this->assertSame($validResult['pageTitle'], $insertedPageTitle);
        $this->assertSame($validResult['pathName'], $insertedPathName);
        $this->assertSame($validResult['pageViewClass'], $insertedPageViewClass);
        $this->assertSame($validResult['pageBuilderClass'], $insertedPageBuilderClass);
        $this->assertSame($validResult['redirectOnError'], $insertedRedirectOnError);
        $this->assertSame($validResult['published'], $insertedPublished);
        $this->assertSame($validResult['pageTypeId'], $insertedPageTypeId);
        
        
    }
    
    
    function testGetAction() {
        // ARRANGE
        // ACT
        // ASSERT
    }
    
    
    function testDeleteAction() {
        // ARRANGE
        // ACT
        // ASSERT
    }

}