<?php
/*
* TestBlogDataResource.php
* Initial commit: Paul Anderson, 4/17/2018
* 
* Unit tests for the BlogDataResource class
*  
*/

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestBlogDataResource extends SqlDependentTestCase {


    function testBuildFields() {
        // Test the BuildFields method of BlogDataResource
        // Expected outcome: BDR holds an array of FieldValidator objects
        // ARRANGE
        $apiPageView = new APIPageView(1);
        $bdr = new BlogDataResource($apiPageView);
        
        // ACT
        $bdr->buildFields();
        
        // ASSERT
        foreach ($bdr->fields as $field) {
            $this->assertTrue(is_subclass_of($field, FieldValidator::class));
        }
    }

    function testPostAction() {
        // Test the PostAction method
        // Cases:
        //  test invalid input
        //  test valid input
        
        // ARRANGE
        $sqlConn = $this->getConnection();
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        
        $apiPageView = new APIPageView(1);
        $apiPageView->setSQLConn($sqlConn);
        
        $bdr = new BlogDataResource($apiPageView);
        
        // Create an unpublished page 
        $unpublishedPageId = intval($this->createUnpublishedPage());
        
        // Create a non-admin user
        $this->createNonAdminUser();
        
        // Mock up post data
        $postData = array('blogName' => 'Good Blog', 'ancestorPageID' => $unpublishedPageId, 'pathname' => 'pathname');
        $badPostData = array('blogName' => 'Bad Blog', 'ancestorPageID' => 666);
        $unpublishedPostData = array('blogName' => 'Unpublished Blog', 'ancestorPageID' => $unpublishedPageId);

        // ACT
        // Attempt post without authentication
        $bdr->dataObjects = $postData;
        $this->unsetBasicAuth();
        $nonAuthenticatedOutput = $bdr->postAction();
        $nonAuthenticatedStatus = $bdr->httpStatus;
        
        // Attempt authentication with bogus user
        $this->setBadAuth();
        $bdr->dataObjects = $postData;
        $unauthorizedOutput = $bdr->postAction();
        $unauthorizedStatus = $bdr->httpStatus;

        // Bad post (unpublished ancestor page, non-admin user without explicit access to ancestor.).
        $this->setNonAdminAuth();   
        $bdr->dataObjects = $unpublishedPostData;
        $unpublishedOutput = $bdr->postAction();
        $unpublishedStatus = $bdr->httpStatus;

        // Attempt with valid user
        $this->setAdminAuth();

        // Bad post (bogus ancestorPageID)
        $bdr->dataObjects = $badPostData;
        $badAncestorOutput = $bdr->postAction();
        $badAncestorStatus = $bdr->httpStatus;
        
        // good post with admin user
        $bdr->dataObjects = $unpublishedPostData;
        $goodOutput = $bdr->postAction();
        $goodStatus = $bdr->httpStatus;
        
        // Extract output from "good" post
        $blogIdStr = key($goodOutput);
        $blogId = intval($blogIdStr);
        $blogData = $goodOutput[$blogIdStr];
        
        // Query the database to ensure a record was inserted
        $blogName = null;
        $blogInserted = false;
        $sql = <<<SQL
        SELECT p.page_title
        FROM blogs b
        JOIN pages p ON b.page_id = p.page_id
        WHERE b.blog_id = :blogId        
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $result = $stmt->fetchAll();
        if ($result)
            $blogName = $result[0][0];
            $blogInserted = true;

        // Post with duplicate pathname
        $bdr->dataObjects = $unpublishedPostData;
        $dupeOutput = $bdr->postAction();
        $dupeStatus = $bdr->httpStatus;
        
        // ASSERT
        // Unauthenticated post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $nonAuthenticatedStatus);
        
        // Invalid auth post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $unauthorizedStatus);
        
        // Access restricted post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $unpublishedStatus);

        // Nonexistent ancestor post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badAncestorStatus);
        
        // Valid post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $goodStatus);
        
        // Record was inserted
        $this->assertTrue($blogInserted);
        // Record has correct data
        $this->assertSame($unpublishedPostData['blogName'], $blogName);

        // duplicate post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $dupeStatus);
        
    }


    /**
     * @depends testPostAction
     */
    function testPutAction() {
        // Unit test the PutAction method.
        // ARRANGE
        // Required objects
        $sqlConn = $this->getConnection();
        $apiPageView = new APIPageView(1);
        $apiPageView->setSQLConn($sqlConn);
        $bdr = new BlogDataResource($apiPageView);
        
        // Acquire an exiting blog ID
        // (Depends on testPostAction())
        $sql = "SELECT blog_id FROM blogs ORDER BY blog_id DESC LIMIT 1";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $blogIdResult = $stmt->fetchAll();
        $blogId = $blogIdResult[0][0];
        
        // most other dependencies are injected by testPostAction.
        $goodInput = array('blogName' => 'New Blog Name');
        $badInput = array();
        
        // ACT
        // ID parameter not set
        $bdr->dataObjects = $goodInput;
        $noIdResult = $bdr->putAction();
        $noIdStatus = $bdr->httpStatus;

        // No auth
        $bdr->parameters['id'] = strval($blogId);
        $bdr->dataObjects = $goodInput;
        $this->unsetBasicAuth();
        $nonAuthenticatedOutput = $bdr->putAction();
        $nonAuthenticatedStatus = $bdr->httpStatus;
        
        // Bad auth
        $bdr->parameters['id'] = $blogId;
        $bdr->dataObjects = $goodInput;
        $this->setBadAuth();
        $unauthorizedResult = $bdr->putAction();
        $unauthorizedStatus = $bdr->httpStatus;

        // Non-admin auth, no access
        $bdr->parameters['id'] = $blogId;
        $bdr->dataObjects = $goodInput;
        $this->setNonAdminAuth();
        $nonAdminAuth = $bdr->putAction();
        $nonAdminStatus = $bdr->httpStatus;
        //die(var_dump($nonAdminAuth));

        // Bogus id parameter
        $bdr->parameters['id'] = '666';
        $bdr->dataObjects = $goodInput;
        $this->setAdminAuth();
        $badIdOutput = $bdr->putAction();
        $badIdStatus = $bdr->httpStatus;

        // Invalid input
        $bdr->parameters['id'] = $blogId;
        $bdr->dataObjects = $badInput;
        $this->setNonAdminAuth();
        $invalidOutput = $bdr->putAction();
        $invalidStatus = $bdr->httpStatus;
        
        // OK input
        $bdr->parameters['id'] = $blogId;
        $bdr->dataObjects = $goodInput;
        $this->setAdminAuth();
        $goodOutput = $bdr->putAction();
        $goodStatus = $bdr->httpStatus;


        // Query database for change
        $name = null;
        $sql = <<<SQL
        SELECT p.page_title
        FROM blogs b
        JOIN pages p ON b.page_id = p.page_id
        WHERE b.blog_id = :blogId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $result = $stmt->fetchAll();
        if ($result)
            $name = $result[0][0];
        
        // ASSERT
        // ID parameter not set
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $noIdStatus);

        // No auth
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $nonAuthenticatedStatus);
        
        // Bad auth
        $this->assertEquals(Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $unauthorizedStatus);

        // Non-admin auth
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);

        // Bogus ID parameter
        $this->assertEquals(Enumeration::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $badIdStatus);

        // Invalid input
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $invalidStatus);

        // good input
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $goodStatus);
        
        // Name changed in database
        $this->assertSame($goodInput['blogName'], $name);
        
    }

}