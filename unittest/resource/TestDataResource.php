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
require_once __DIR__ . '../../ServerDependentTestCase.php';
include_once LIBPATH.'php/resource/abstract/DataResource.php';

// testable data resource
class TestableDataResource extends DataResource {
    // No overrides or additional functionality.
}

class TestDataResource extends SqlDependentTestCase {
    /*
    public function testAuthenticateUser() {
        
    }
    */
    
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
             'valid' => 666
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

    /*
    public function testGetSubResources() {
        
    }
    */

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

    /*
    public function testGetResource() {
        
    }
    */
}
