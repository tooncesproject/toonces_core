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
        $blogPageId = null;
        $blogInserted = false;
        $sql = "SELECT page_id FROM blogs WHERE blog_id = :blogId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $result = $stmt->fetchAll();
        if ($result)
            $blogPageId = $result[0][0];
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

        // duplicate post
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $dupeStatus);
        
        
    }
    
    
}