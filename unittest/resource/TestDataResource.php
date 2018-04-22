<?php
/*
 * @author paulanderson
 * TestDataResource.php
 * Initial commit: 4/21/2018
 * 
 * Unit tests for the DataResource abstract class.
 * */


use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';
include_once LIBPATH.'php/resource/abstract/DataResource.php';

// testable data resource
class TestableDataResource extends DataResource {
    // No overrides or additional functionality.
}

class TestDataResource extends SqlDependentTestCase {

    public function testAuthenticateUser() {
        // ARRANGE
        // Instantiate with a PageView object
        $apiPageView = new APIPageView(1);
        $dr = new TestableDataResource($apiPageView);
        // Set up SQL connection
        $sqlConn = $this->getConnection();
        $apiPageView->setSQLConn($sqlConn);
        
        // Set up Toonces database fixture
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        
        // ACT
        // Attempt login with no user
        if (array_key_exists('PHP_AUTH_USER', $_SERVER))
            unset($_SERVER['PHP_AUTH_USER']);
        
         if (array_key_exists('PHP_AUTH_PW', $_SERVER))
             unset($_SERVER['PHP_AUTH_PW']);
        
        $noLogin = $dr->authenticateUser();
             
        // Attempt authentication with bogus user
        $_SERVER['PHP_AUTH_USER'] = 'badguy@evil.com';
        $_SERVER['PHP_AUTH_PW'] = 'bogusPassword';
        
        $badLogin = $dr->authenticateUser();
        
        // Attempt with valid user
        $_SERVER['PHP_AUTH_USER'] = 'email@example.com';
        $_SERVER['PHP_AUTH_PW'] = 'mySecurePassword';
        
        $goodLogin = $dr->authenticateUser();
        
        $this->destroyTestDatabase();
        
        // ASSERT
        $this->assertNull($noLogin);
        $this->assertNull($badLogin);
        $this->assertTrue(is_int(intval($goodLogin)));
    }


    public function testValidateHeaders() {
        // ARRANGE
        // Instantiate with a PageView object
        $apiPageView = new APIPageView(1);
        $dr = new TestableDataResource($apiPageView);
        

        // ACT
        // Go without header
        $invalidResult = $dr->validateHeaders();
        
        // Inject the required header for a valid result
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $validResult = $dr->validateHeaders();

        // ASSERT
        $this->assertFalse($invalidResult);
        $this->assertTrue($validResult);
    }


    public function testValidateIntParameter() {
        // Test the validateIntParameter function

        // ARRANGE
        // Instantiate with a PageView object
        $apiPageView = new APIPageView(1);
        $dr = new TestableDataResource($apiPageView);
        
        $parameterArray = array(
             'valid' => '666'
            ,'invalid' => 'foo'
            ,'null' => null
        );
        
        $dr->parameters = $parameterArray;

        // ACT
        $validResult = $dr->validateIntParameter('valid');
        $stringInvalidResult = $dr->validateIntParameter('invalid');
        $nullInvalidResult = $dr->validateIntParameter('null');
        $nonexistentResult = $dr->validateIntParameter('barf');

        // ASSERT
        $this->assertSame($validResult, 666);
        $this->assertEquals($stringInvalidResult, 0);
        $this->assertEquals($nullInvalidResult, 0);
        $this->assertNull($nonexistentResult);
        
    }

    
    public function testGetSubResources() {
        // ARRANGE

        // Set up SQL connection
        $sqlConn = $this->getConnection();
        
        
        // Set up Toonces database fixture
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        // We'll use the 'coreservices' API root for our test.
        // The APIPageView object needs to know its page ID.
        $sql = <<<SQL
        SELECT
            page_id
        FROM
            toonces.pages
        WHERE
            pathname = 'coreservices'
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $pageId = $result[0][0];
        $apiPageView = new APIPageView($pageId);
        $apiPageView->setSQLConn($sqlConn);
        $dr = new TestableDataResource($apiPageView);
        
        // ACT
        $_SERVER['PHP_AUTH_USER'] = 'badhacker@asshole.com';
        $_SERVER['PHP_AUTH_PW'] = '12345';
        $resultUnauthenticated = $dr->getSubResources();
        $dataUnauthenticated = $dr->dataObjects;
        
        // Authenticate and then do it again.
        $_SERVER['PHP_AUTH_USER'] = 'email@example.com';
        $_SERVER['PHP_AUTH_PW'] = 'mySecurePassword';
        $resultAuthenticated = $dr->getSubResources();
        $dataAuthenticated = $dr->dataObjects;
        $statusAuthenticated = $dr->httpStatus;
        
        $this->destroyTestDatabase();

        // ASSERT
        $this->assertFalse($resultUnauthenticated);
        $this->assertEquals(0, count($dataUnauthenticated));
        
        $this->assertTrue($resultAuthenticated);
        $this->assertGreaterThan(0, count($dataAuthenticated));
        $this->assertEquals(Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse'), $statusAuthenticated);
    }
    

    public function testValidateData () {
        // ARRANGE
        // Instantiate base objects
        $apiPageView = new APIPageView(1);
        $dr = new TestableDataResource($apiPageView);
        
        // Set up the DataResource object's field validators.
        $nullableField = new StringFieldValidator();
        $nullableField->maxLength = 50;
        $nullableField->allowNull=true;
        $dr->fields['nullableField'] = $nullableField;
        
        $requiredField = new IntegerFieldValidator();
        $dr->fields['requiredField'] = $requiredField;
        
        // Build several objects to inject as data to be validated.
        $nonArrayObject = 'I am not an array';
        $validWithAllFields = array('nullableField' => 'foo', 'requiredField' => 666);
        $validWithRequiredField = array('requiredField' => 123);
        $invalidMissingField = array('nullableField' => 'butt');
        $invalidBadRequiredField = array('nullableField' => 'foo', 'requiredField' => 'boo');
        $invalidBadNullableField = array('nullableField' => 123, 'requiredField' => 456);
        
        // ACT
        $nonArrayResult = $dr->validateData($nonArrayObject);
        $nonArrayStatus = $dr->httpStatus;
        
        $validAllResult = $dr->validateData($validWithAllFields);

        $validRequiredResult = $dr->validateData($validWithRequiredField);
        
        $missingFieldresult = $dr->validateData($invalidMissingField);
        $missingFieldStatus = $dr->httpStatus;
        
        $badRequiredResult = $dr->validateData($invalidBadRequiredField);
        $badRequiredStatus = $dr->httpStatus;
        
        $badNullableResult = $dr->validateData($invalidBadRequiredField);
        $badNullableStatus = $dr->httpStatus;

        // ASSERT
        $this->assertFalse($nonArrayResult);
        $this->assertEquals($nonArrayStatus, Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'));
        
        $this->assertTrue($validRequiredResult);
        $this->assertTrue($validRequiredResult);
        
        $this->assertFalse($missingFieldresult);
        $this->assertEquals($missingFieldStatus, Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'));
        
        $this->assertFalse($badRequiredResult);
        $this->assertEquals($badRequiredStatus, Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'));
        
        $this->assertFalse($badNullableResult);
        $this->assertEquals($badNullableStatus, Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'));

    }

    public function testGetResource() {
        // This test also covers the "action" methods of the abstract class.
        // 
        
        // ARRANGE
        // Instantiate base objects
        $apiPageView = new APIPageView(1);
        $dr = new TestableDataResource($apiPageView);
        $testObjectArray = array('testObject' => 'foo');
        $dr->dataObjects = $testObjectArray;
        
        // Inject HTTP host
        $_SERVER['HTTP_HOST'] = 'example.com';
        
        // Inject Resource URI
        $dr->resourceURI = 'path';
        
        // ACT
        // Call the method without the default valid header
        if (isset($_SERVER['CONTENT_TYPE']))
            $_SERVER['CONTENT_TYPE'] = 'foo';

        $noHeaderResult = $dr->getResource();
        $noHeaderStatus = $dr->httpStatus;
        
        // Try it with valid content type header but no HTTP verb
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        if (isset($_SERVER['REQUEST_METHOD']))
            unset($_SERVER['REQUEST_METHOD']);
        $caughtException = false;
        try {
            $dr->getResource;
        } finally {
            $caughtException = true;
        }
        
        // Call the method with each "supported" HTTP verb.
        // Also, we include the required content-type header.
        
        // GET
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getResult = $dr->getResource(); 
        $getStatus = $dr->httpStatus;
        $httpURL = $dr->resourceURL;
        
        // POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTPS'] = 'on';
        $postResult = $dr->getResource();
        $postStatus = $dr->httpStatus;
        $httpsURL = $dr->resourceURL;
        
        // HEAD
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $headResult = $dr->getResource();
        $headStatus = $dr->httpStatus;
        
        // PUT
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $putResult = $dr->getResource();
        $putStatus = $dr->httpStatus;
        
        // OPTIONS
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $optionsResult = $dr->getResource();
        $optionsStatus = $dr->httpStatus;
        
        // DELETE
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $deleteResult = $dr->getResource();
        $deleteStatus = $dr->httpStatus;
        
        // CONNECT
        $_SERVER['REQUEST_METHOD'] = 'CONNECT';
        $connectResult = $dr->getResource();
        $connectStatus = $dr->httpStatus;

        // ASSERT
        // No content-type header
        $this->assertEquals($noHeaderStatus, Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'));
        
        // No HTTP verb
        $this->assertTrue($caughtException);

        // GET
        // Only one assertion for $testObjectArray - We just wanna know it will return something.
        $this->assertSame($testObjectArray, $getResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $getStatus);
        // One assertion for 'http' URL scheme - We won't repeat this.
        $this->assertSame($httpURL, 'http://example.com/path');
        
        // POST
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $postStatus);
        // One assertion for 'https' URL scheme - We won't repeat this.
        $this->assertSame($httpsURL, 'https://example.com/path');

        // HEAD
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $headStatus);
        
        // PUT
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $putStatus);
        
        // OPTIONS
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $optionsStatus);
        
        // DELETE
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $deleteStatus);
        
        // CONNECT
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $connectStatus);
    }

}
