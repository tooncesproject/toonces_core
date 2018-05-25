<?php
/**
 * @author paulanderson
 * ApiDataValidator.php
 * Initial Commit: Paul Anderson, 5/25/2018
 *
 * Abstract class actuating validation operations for DataResource inputs.
 *
 */

include_once LIBPATH . 'php/toonces.php';


abstract class ApiDataValidator implements iApiDataValidator {


    var $fieldValidators = array();

    /**
     * @param string $paramKey
     * @param iFieldValidator $paramField
     * @throws Exception
     */
    public function addFieldValidator($paramKey, $paramField) {

        // field must implement iFieldValidator
        if (!$paramField instanceof iFieldValidator)
            throw new Exception('Error: Validator field objects must implement the iFieldValidator interface.');

        $this->fieldValidators[$paramKey] = $paramField;
    }


    public function buildFields() {
        // Override this to implement a concrete subclass.
    }


    public function validateDataStructure($paramData) {
        return is_array($paramData);
    }


    /**
     * @param array $paramData
     * @return array
     */
    public function getMissingRequiredFields($paramData) {

        $this->buildFields();
        $missingFields = array();

        foreach ($this->fieldValidators as $key => $fieldValidator) {
            // Is the data missing any required fields?
            $fieldExists = array_key_exists($key, $paramData);

            if (!$fieldValidator->allowNull && !$fieldExists) {
                // Required key missing
                array_push($missingFields, $key);
            }
        }

        return $missingFields;
    }


    /**
     * @param array $paramData
     * @return array
     */
    public function getInvalidFields($paramData) {

        $this->buildFields();
        $invalidFields = array();

        foreach ($this->fieldValidators as $key => $fieldValidator) {
            $fieldExists = array_key_exists($key, $paramData);
            // Is the input data valid, per the data object's requirements?
            if ($fieldExists) {
                if (!$fieldValidator->validateData($paramData[$key])) {
                    // Field does not pass validation
                    $invalidFields[$key] = $fieldValidator->statusMessage;
                }
            }
        }

        return $invalidFields;
    }
}
