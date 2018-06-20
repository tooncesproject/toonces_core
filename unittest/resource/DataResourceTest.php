<?php
/**
 * @author paulanderson
 * DataResourceTest.php
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
class ConcreteDataResource extends DataResource {
    // No overrides or additional functionality.
}

class TestApiDataValidator extends ApiDataValidator {

    /**
     * @throws Exception
     */
    function buildFields() {

        $integerFieldValidator = new IntegerFieldValidator(false);
        $this->addFieldValidator('requiredField', $integerFieldValidator);

        $stringFieldValidator = new StringFieldValidator(true);
        $this->addFieldValidator('nullableField', $stringFieldValidator);

    }
}

class DataResourceTest extends SqlDependentTestCase {


    public function testValidateHeaders() {
        // ARRANGE
        $dr = new ConcreteDataResource();

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
        $dr = new ConcreteDataResource();

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
        // The JsonRenderer object needs to know its resource ID.
        $sql = <<<SQL
        SELECT
            resource_id
        FROM
            toonces.resource
        WHERE
            pathname = 'coreservices'
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $resourceId = $result[0][0];

        $dr = new ConcreteDataResource();
        $dr->conn = $sqlConn;
        $dr->resourceId = $resourceId;

        // ACT
        $_SERVER['PHP_AUTH_USER'] = 'badhacker@asshole.com';
        $_SERVER['PHP_AUTH_PW'] = '12345';
        $resultUnauthenticated = $dr->getSubResources();
        $dataUnauthenticated = $dr->resourceData;

        // Authenticate and then do it again.
        $_SERVER['PHP_AUTH_USER'] = $GLOBALS['TOONCES_USERNAME'];
        $_SERVER['PHP_AUTH_PW'] = $GLOBALS['TOONCES_PASSWORD'];
        $resultAuthenticated = $dr->getSubResources();
        $dataAuthenticated = $dr->resourceData;
        $statusAuthenticated = $dr->httpStatus;

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
        $dr = new ConcreteDataResource();

        // Set up the DataResource object's Data Validator.
        $dr->apiDataValidator = new TestApiDataValidator();

        // Build several objects to inject as data to be validated.
        $nonArrayObject = 'I am not an array';
        $validWithRequiredField = array('requiredField' => 123);
        $invalidMissingField = array('nullableField' => 'butt');
        $invalidBadRequiredField = array('nullableField' => 'foo', 'requiredField' => 'boo');
        $invalidBadNullableField = array('nullableField' => 123, 'requiredField' => 456);

        // ACT
        // data failing ValidateDataStructure generates 400 error and returns false
        $nonArrayResult = $dr->validateData($nonArrayObject);
        $nonArrayStatus = $dr->httpStatus;

        // Valid data returns true
        $validDataResult = $dr->validateData($validWithRequiredField);

        // Data with missing field generates 400 error and returns false
        $missingFieldresult = $dr->validateData($invalidMissingField);
        $missingFieldStatus = $dr->httpStatus;

        // Data with invalid required field generates 400 error and returns false
        $badRequiredResult = $dr->validateData($invalidBadRequiredField);
        $badRequiredStatus = $dr->httpStatus;

        // Data with invalid nullable field generates 400 error and returns false
        $badNullableResult = $dr->validateData($invalidBadNullableField);
        $badNullableStatus = $dr->httpStatus;

        // ASSERT
        // data failing ValidateDataStructure generates 400 error and returns false
        $this->assertFalse($nonArrayResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $nonArrayStatus);

        // Valid data returns true
        $this->assertTrue($validDataResult);

        // Data with missing field generates 400 error and returns false
        $this->assertFalse($missingFieldresult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $missingFieldStatus);

        // Data with invalid field generates 400 error and returns false
        $this->assertFalse($badRequiredResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badRequiredStatus);

        // Data with invalid nullable field generates 400 error and returns false
        $this->assertFalse($badNullableResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $badNullableStatus);

    }


    /**
     * @depends testGetSubResources
     */
    function testGetResource() {
        // ARRANGE
        // See testGetSubResources for fixture injection
        $dr = new ConcreteDataResource();
        $dr->conn = $this->getConnection();
        $dr->httpMethod = 'GET';
        $testData = array('foo' => 'bar');
        $dr->resourceData = $testData;

        // ACT
        // Call the method without the default valid header
        if (isset($_SERVER['CONTENT_TYPE']))
            $_SERVER['CONTENT_TYPE'] = 'foo';

        $noHeaderResult = $dr->getResource();
        $noHeaderStatus = $dr->httpStatus;

        // Call the method with the default valid header set - Expect parent class (ApiResource) operations
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $dr->statusMessage = null;
        $headerResult = $dr->getResource();
        $headerStatus = $dr->httpStatus;

        // ASSERT
        // Call the method without the default valid header
        $this->assertEquals(Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse'), $noHeaderStatus);

        // Call the method with the default valid header set - Expect parent class (ApiResource) operations
        $this->assertSame($testData, $headerResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $headerStatus);

        // tear down the fixture
        $this->destroyTestDatabase();
    }


}
