<?php
/**
 * @author paulanderson
 * HtmlFieldValidator.php
 * Initial commit: Paul Anderson, 5/5/2018
 * 
 * Extends StringFieldValidator to validate HTML strings.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class HtmlFieldValidator extends StringFieldValidator implements  iFieldValidator {
    
    function validateHtml($string) {
        /**
         * Ripped off from https://stackoverflow.com/questions/3167074/which-function-in-php-validate-if-the-string-is-valid-html
         * 
         */
        
        $start =strpos($string, '<');
        $end  =strrpos($string, '>',$start);
        
        $len=strlen($string);
        
        if ($end !== false) {
            $string = substr($string, $start);
        } else {
            $string = substr($string, $start, $len-$start);
        }
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_string($string);
        return count(libxml_get_errors())==0;
    }
    
    public function validateData($data) {
        /**
         * @override StringFieldValidator->validateData
         * 
         */
        
        // First - call parent to verify that the data is a string and under character limit (if applicable)
        $dataValid = parent::validateData($data);
        
        if ($dataValid) 
            $dataValid = $this->validateHtml($data);
        
        if (!$dataValid) {
            $this->statusMessage = 'Validation failed; the string is not well-formed HTML.';
        }
        return $dataValid;
    }
}