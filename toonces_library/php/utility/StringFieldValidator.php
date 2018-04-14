<?php
/*
 * StringFieldValidator.php
 * Initial commit: Paul Anderson, 4/10/2018
 *
 * iFieldValidator-compliant class providing validation for strings in a REST API or other application.
 */

require_once LIBPATH.'php/toonces.php';

class StringFieldValidator implements iFieldValidator {
    
    public $allowNull;
    public $maxLength;
    public function validateData($data) {
        // Verifies that the data is a string and (if applicable) does not exceed the max length.
        $dataValid = false;
        
        // Is the field nullable?
        if ($this->allowNull && empty($data)) {
            $dataValid = true;
        } else {
            do {
                // Data is a string?
                if (!is_string($data)) {
                    $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                    $this->statusMessage = 'This object must be a string.';
                    break;
                }
                
                // If applicable, length does not exceed the specified maximum?
                if ($this->maxLength) {
                    if (strlen($data) > $this->maxLength) {
                        $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                        $this->statusMessage = 'The maximum allowed character length of this field is ' . strval($this->maxLength);
                        break;
                    }
                }
                
                // If we're here, data is okie dokie
                $dataValid = true;
            } while (false);
        }
        return $dataValid;
        
    }
    
}