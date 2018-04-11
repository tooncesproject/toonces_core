<?php
/*
 * DataResource.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * iResource implementation representing nested data objects.
 *
 */

include_once LIBPATH.'php/toonces.php';

class DataResource implements iResource
{
    var $dataObjects = array();
    var $fields = array();
    var $resourceID;
    var $statusMessage = '';
    var $httpStatus;

    public function validateData($data) {
        // Iterate through keys in dataObjects array
        $postValid = false;
        $missingFields = array();
        $invalidFields = array();
        
        // Check validation.
        do {
            // We go through each requirement in order of priority
            // The first requirement for DataResource is that the data is an array
            // (JSON should already be validated and converted to array at this point).
            if (!is_array($data)) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'This object must be an array.';
                break;
            }
            
            // Iterate through each field and check for validity.
            foreach ($this->fields as $key => $field) {
                
                // Is the POST/PUT data missing any required fields?
                $fieldExists = array_key_exists($key, $data);
                if ($field->isRequired && !$fieldExists) {
                    // Required key missing
                    array_push($missingFields, $key);
                }
                
                // Is the input data valid, per the data object's requirements?
                if ($fieldExists) {
                    if (!$field->validateData($data[$key])) {
                        // Field does not pass validation
                        $invalidFields[$key] = $field->statusMessage;
                    }
                }
            }

            if (count($missingFields)) {
                // One or more required fields is missing - Break here.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'One or more required fields is missing: ' . implode(', ', $missingFields);
                break;
            }

            if (count($invalidFields)) {
                // One or more fields had bogus data.
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $errorArray = array();
                foreach($invalidFields as $invalidKey => $value) {
                    array_push($errorArray, $invalidKey . ': ' . $value);
                }
                $this->statusMessage = implode(', ', $errorArray);
                break;
            }
            // If we've made it this far, we're OK.
            $postValid = true;
            
        } while (false);
        
        return $postValid;
    }
    
    public function addElement ($element) {
        array_push($this->dataObjects,$element);
    }
    
    // execution method
    public function getResource() {
        return $this->dataObjects;
    }
    
    public function postResource() {
        
    }


}
