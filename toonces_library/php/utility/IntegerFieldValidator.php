<?php
/*
 * IntegerFieldValidator.php
 * Initial commit: Paul Anderson, 4/13/2018
 *
 * iFieldValidator-compliant class providing validation for integers in a REST API or other application.
 */

require_once LIBPATH.'php/toonces.php';

class IntegerFieldValidator implements iFieldValidator {
    
    public $allowNull;

    public function validateData($data) {
        // Verifies that the data is a string and (if applicable) does not exceed the max length.
        $dataValid = false;
        
        // Is the field nullable?
        if ($this->allowNull && empty($data)) {
            $dataValid = true;
        } else {
            do {
                // Data is a string?
                if (!is_int($data)) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                    $this->statusMessage = 'This object must be a string.';
                    break;
                }
                
                // If we're here, data is okie dokie
                $dataValid = true;
            } while (false);
        }
        return $dataValid;
        
    }
}
