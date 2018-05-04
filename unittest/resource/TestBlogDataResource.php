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
        $jsonPageView = new JsonPageView(1);
        $bdr = new BlogDataResource($jsonPageView);

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

        $jsonPageView = new JsonPageView(1);
        $jsonPageView->setSQLConn($sqlConn);

        $bdr = new BlogDataResource($jsonPageView);

        // Create an unpublished page
        $unpublishedPageId = intval($this-> createPage(false));

        // Create a non-admin user
        $this->createNonAdminUser();

        // Mock up post data
        $postData = array('blogName' => 'Good Blog', 'ancestorPageID' => $unpublishedPageId, 'pathname' => 'pathname');
        $badPostData = array('blogName' => 'Bad Blog', 'ancestorPageID' => 666);
        $unpublishedPostData = array('blogName' => 'Unpublished Blog', 'ancestorPageID' => $unpublishedPageId);

        // ACT
        // Attempt post without authentication
        $bdr->resourceData = $postData;
        $this->unsetBasicAuth();
        $nonAuthenticatedOutput = $bdr->postAction();
        $nonAuthenticatedStatus = $bdr->httpStatus;

        // Attempt authentication with bogus user
        $this->setBadAuth();
        $bdr->resourceData = $postData;
        $unauthorizedOutput = $bdr->postAction();
        $unauthorizedStatus = $bdr->httpStatus;

        // Bad post (unpublished ancestor page, non-admin user without explicit access to ancestor.).
        $this->setNonAdminAuth();
        $bdr->resourceData = $unpublishedPostData;
        $unpublishedOutput = $bdr->postAction();
        $unpublishedStatus = $bdr->httpStatus;

        // Attempt with valid user
        $this->setAdminAuth();

        // Bad post (bogus ancestorPageID)
        $bdr->resourceData = $badPostData;
        $badAncestorOutput = $bdr->postAction();
        $badAncestorStatus = $bdr->httpStatus;

        // good post with admin user
        $bdr->resourceData = $unpublishedPostData;
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
        $bdr->resourceData = $unpublishedPostData;
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
        $this->assertEquals(Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse'), $dupeStatus);

    }


    /**
     * @depends testPostAction
     */
    function testPutAction() {
        // Unit test the PutAction method.
        // ARRANGE
        // Required objects
        $sqlConn = $this->getConnection();
        $jsonPageView = new JsonPageView(1);
        $jsonPageView->setSQLConn($sqlConn);
        $bdr = new BlogDataResource($jsonPageView);

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
        $bdr->resourceData = $goodInput;
        $noIdResult = $bdr->putAction();
        $noIdStatus = $bdr->httpStatus;

        // No auth
        $bdr->parameters['id'] = strval($blogId);
        $bdr->resourceData = $goodInput;
        $this->unsetBasicAuth();
        $nonAuthenticatedOutput = $bdr->putAction();
        $nonAuthenticatedStatus = $bdr->httpStatus;

        // Bad auth
        $bdr->parameters['id'] = $blogId;
        $bdr->resourceData = $goodInput;
        $this->setBadAuth();
        $unauthorizedResult = $bdr->putAction();
        $unauthorizedStatus = $bdr->httpStatus;

        // Non-admin auth, no access
        $bdr->parameters['id'] = $blogId;
        $bdr->resourceData = $goodInput;
        $this->setNonAdminAuth();
        $nonAdminAuth = $bdr->putAction();
        $nonAdminStatus = $bdr->httpStatus;

        // Bogus id parameter
        $bdr->parameters['id'] = '666';
        $bdr->resourceData = $goodInput;
        $this->setAdminAuth();
        $badIdOutput = $bdr->putAction();
        $badIdStatus = $bdr->httpStatus;

        // Invalid input
        $bdr->parameters['id'] = $blogId;
        $bdr->resourceData = $badInput;
        $this->setNonAdminAuth();
        $invalidOutput = $bdr->putAction();
        $invalidStatus = $bdr->httpStatus;

        // OK input
        $bdr->parameters['id'] = $blogId;
        $bdr->resourceData = $goodInput;
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

    /**
     * @depends testPutAction
     */
    function testGetAction() {
        // Test the getAction method
        // ARRANGE
        $sqlConn = $this->getConnection();
        $jsonPageView = new JsonPageView(1);
        $jsonPageView->setSQLConn($sqlConn);
        $bdr = new BlogDataResource($jsonPageView);

        // Acquire an exiting blog ID
        // (Depends on testPostAction())
        $sql = "SELECT blog_id FROM blogs ORDER BY blog_id DESC LIMIT 1";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $blogIdResult = $stmt->fetchAll();
        $blogId = $blogIdResult[0][0];

        // Create a 2nd blog but make it unpublished
        $this->setAdminAuth();
        $postData = array(
             'blogName' => 'Another Unpublished Blog'
            ,'pathName' => 'anotherunpublishedblog'
            ,'ancestorPageID' => 1
        );
        $bdr->resourceData = $postData;
        $postResponse = $bdr->postAction();
        $newBlogId =  key($postResponse);
        $newBlogIdStr = strval($newBlogId);
        $blogData = $postResponse[$newBlogIdStr];
        $newPageId = $blogData['pageID'];

        $sql = "UPDATE pages SET published = FALSE WHERE page_id = :newPageId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('newPageId' => $newPageId));

        // ACT
        // Bad authenticated GET, no parameters
        $this->setBadAuth();
        $bdr->resourceData = array();
        $badAuthNpOutput = $bdr->getAction();
        $badAuthNpStatus = $bdr->httpStatus;

        // Unauthenticated GET with unpublished ID parameter
        $this->unsetBasicAuth();
        $bdr->resourceData = array();
        $bdr->parameters['id'] = $newBlogIdStr;
        $noAuthUnpublishedOutput = $bdr->getAction();
        $noAuthUnpublishedStatus = $bdr->httpStatus;

        // Non-admin authenticated GET with unpublished ID parameter
        $this->setNonAdminAuth();
        $bdr->resourceData = array();
        $bdr->parameters['id'] = $newBlogIdStr;
        $nonAdminUnpubOutput = $bdr->getAction();
        $nonAdminUnpubStatus = $bdr->httpStatus;

        // Authenticated GET with bogus ID parameter
        $this->setAdminAuth();
        $bdr->resourceData = array();
        $bdr->parameters['id'] = '666';
        $bogusParamOutput = $bdr->getAction();
        $bogusParamStatus = $bdr->httpStatus;

        // Admin authenticated GET, no parameters
        $this->setAdminAuth();
        $bdr->resourceData = array();
        $bdr->parameters = array();
        $adminAuthNpOutput = $bdr->getAction();
        $adminAuthNpStatus = $bdr->httpStatus;

        // Admin authenticated GET with ID parameter
        $this->setAdminAuth();
        $bdr->resourceData = array();
        $bdr->parameters['id'] = $newBlogIdStr;
        $adminParamOutput = $bdr->getAction();
        $adminParamStatus = $bdr->httpStatus;

        // Unauthenticated GET, no parameters
        $this->unsetBasicAuth();
        $bdr->resourceData = array();
        $bdr->parameters = array();
        $noParamsOutput = $bdr->getAction();
        $noParamsStatus = $bdr->httpStatus;

        // ASSERT
        // Bad authenticated GET, no parameters
        $this->assertFalse(isset($badAuthNpOutput[$newBlogIdStr]));

        // Unauthenticated GET with unpublished ID parameter
        $this->assertFalse(isset($noAuthUnpublishedOutput[$newBlogIdStr]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $noAuthUnpublishedStatus);

        // Non-admin authenticated GET with unpublished ID parameter
        $this->assertFalse(isset($nonAdminUnpubOutput[$newBlogIdStr]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminUnpubStatus);

        // Authenticated GET with bogus ID parameter
        $this->assertFalse(isset($bogusParamOutput[$newBlogIdStr]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $bogusParamStatus);

        // Admin authenticated GET, no parameters
        $this->assertTrue(isset($adminAuthNpOutput[$newBlogId]));
        $this->assertTrue(isset($adminAuthNpOutput[$blogId]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $adminAuthNpStatus);

        // Admin authenticated GET with ID parameter
        $this->assertTrue(isset($adminParamOutput[$newBlogIdStr]));
        $this->assertFalse(isset($adminParamOutput[strval($blogId)]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $adminParamStatus);

        // (additionally, let's check some of the data)
        $this->assertSame($postData['blogName'], $adminParamOutput[$newBlogIdStr]['blogName']);

        // Unauthenticated GET, no parameters
        $this->assertFalse(isset($noParamsOutput[$newBlogIdStr]));
        $this->assertTrue(isset($noParamsOutput[strval($blogId)]));
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $noParamsStatus);

    }

    /**
     * @depends testGetAction
     */
    function testDeleteAction() {
        // Unit tests the DeleteAction method
        // ARRANGE
        $sqlConn = $this->getConnection();
        $jsonPageView = new JsonPageView(1);
        $jsonPageView->setSQLConn($sqlConn);
        $bdr = new BlogDataResource($jsonPageView);

        // Acquire an exiting blog ID
        // (Depends on testPostAction())
        $sql = "SELECT blog_id FROM blogs ORDER BY blog_id DESC LIMIT 1";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $blogIdResult = $stmt->fetchAll();
        $blogId = $blogIdResult[0][0];

        // ACT
        // DELETE method without id parameter set
        $this->setAdminAuth();
        $noParamResult = $bdr->deleteAction();
        $noParamStatus = $bdr->httpStatus;

        // DELETE unpublished page without any login
        $this->unsetBasicAuth();
        $bdr->parameters['id'] = strval($blodId);
        $noLoginResult = $bdr->deleteAction();
        $noLoginStatus = $bdr->httpStatus;

        $sql = "SELECT blog_id FROM blogs WHERE blog_id = :blogId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $sqlResult = $stmt->fetchAll();
        $blogDeletedWithoutLogin = ($sqlResult) ? false : true;

        // DELETE unpublished page without admin login
        $this->setNonAdminAuth();
        $bdr->parameters['id'] = strval($blodId);
        $nonAdminResult = $bdr->deleteAction();
        $nonAdminStatus = $bdr->httpStatus;

        $sql = "SELECT blog_id FROM blogs WHERE blog_id = :blogId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $sqlResult = $stmt->fetchAll();
        $blogDeletedWithoutAdminLogin = ($sqlResult) ? false : true;

        // DELETE with bogus id parameter
        $this->setAdminAuth();
        $bdr->parameters['id'] = '12345';
        $bogusParamResult = $bdr->deleteAction();
        $bogusParamStatus = $bdr->httpStatus;

        $sql = "SELECT blog_id FROM blogs WHERE blog_id = :blogId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $sqlResult = $stmt->fetchAll();
        $blogDeletedWithBogusId = ($sqlResult) ? false : true;

        // Legit delete
        $bdr->parameters['id'] = strval($blogId);
        $legitResult = $bdr->deleteAction();
        $legitStatus = $bdr->httpStatus;

        $sql = "SELECT blog_id FROM blogs WHERE blog_id = :blogId";
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('blogId' => $blogId));
        $sqlResult = $stmt->fetchAll();
        $blogDeletedLegitimately = ($sqlResult) ? false : true;


        //ASSERT
        // DELETE method without id parameter set
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $noParamStatus);

        // DELETE unpublished page without any login
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse'), $noLoginStatus);
        $this->assertFalse($blogDeletedWithoutLogin);

        // DELETE unpublished page without admin login
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $nonAdminStatus);
        $this->assertFalse($blogDeletedWithoutAdminLogin);

        // DELETE with bogus id parameter
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_404_NOT_FOUND', 'EnumHTTPResponse'), $bogusParamStatus);
        $this->assertFalse($blogDeletedWithBogusId);

        // Legit delete
        $this->assertEquals(EnumHTTPResponse::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse'), $legitStatus);
        $this->assertTrue($blogDeletedLegitimately);

        // All done. Tear down the fixture.
        $this->destroyTestDatabase();


    }

}
