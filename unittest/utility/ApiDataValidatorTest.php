<?php
/**
 * @author paulanderson
 * ApiDataValidatorTest.php
 * Initial Commit: Paul Anderson, 5/25/18
 *
 * Unit tests for the ApiDataValidator class.
 *
 */


use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class ConcreteApiDataValidator extends ApiDataValidator {

    /**
     * @throws Exception
     */
    function buildFields() {

        $integerFieldValidator = new IntegerFieldValidator(false);
        $this->addFieldValidator('integerField', $integerFieldValidator);

        $stringFieldValidator = new StringFieldValidator(true);
        $this->addFieldValidator('stringField', $stringFieldValidator);

    }
}


class ApiDataValidatorTest extends TestCase {

    /**
     * @expectedException Exception
     */
    function testAddFieldValidatorException() {
        // ARRANGE
        $validator = new ConcreteApiDataValidator();
        $notAFieldValidator = 'foo';

        // ACT
        $validator->addFieldValidator('bogusField', $notAFieldValidator);
    }


    function testValidateDataStructure() {
        // ARRANGE
        $validator = new ConcreteApiDataValidator();
        $validData = array('integerField' => 1, 'stringField' => 'foo');
        $invalidData = 'foo';

        // ACT
        $validResult = $validator->validateDataStructure($validData);
        $invalidResult = $validator->validateDataStructure($invalidData);

        // ASSERT
        $this->assertTrue($validResult);
        $this->assertFalse($invalidResult);
    }


    function testGetMissingRequiredFields() {
        // ARRANGE
        $validator = new ConcreteApiDataValidator();
        $validData = array('integerField' => 1, 'stringField' => 'foo');
        $invalidData = array('integerField' => 1);

        // ACT
        $nothingInvalid = $validator->getMissingRequiredFields($validData);
        $somethingInvalid = $validator->getMissingRequiredFields($invalidData);

        // ASSERT
        $this->assertEmpty($nothingInvalid);
        $this->assertArrayHasKey('integerField', $somethingInvalid);
    }


    function testGetInvalidFields() {
        // ARRANGE
        $validator = new ConcreteApiDataValidator();
        $validData = array('integerField' => 1, 'stringField' => 'foo');
        $invalidData = array('integerField' => 1, 'stringField' => 666);


        // ACT
        $nothingInvalid = $validator->getInvalidFields($validData);
        $somethingInvalid = $validator->getInvalidFields($invalidData);

        // ASSERT
        $this->assertEmpty($nothingInvalid);
        $this->assertArrayHasKey('stringField', $somethingInvalid);
    }

}
