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
    
    function recursivelyDetectJs($tidyNode) {
        // Iterates recursively through tidyNode objects and detects whether there is a 
        // JavaScript node.

        $hasScript = false;
        // Is the node's name 'script'?
        if ($tidyNode->name == 'script') {
            $hasScript = true;
        } else {
                // Does the node have any children?
                if ($tidyNode->hasChildren()) {
                    $children = $tidyNode->child;
                    foreach ($children as $child) {
                        $hasScript = $this->recursivelyDetectJs($child);
                        if ($hasScript) {
                            break;
                        }
                        
                    }
                }
        }
        return $hasScript;
        
    }
    
    function detectScripts($data) {
        /**
         * Checks if there's any Javascript.
         * 
         */
        // Parse the HTML using tidy.
        $tidy = tidy_parse_string($data);
        
        $root = $tidy->root();
        $scriptDetected = $this->recursivelyDetectJs($root);

        return !$scriptDetected;
        
        
    }
    
    public function validateData($data) {
        /**
         * @override StringFieldValidator->validateData
         * 
         */
        
        // First - call parent to verify that the data is a string and under character limit (if applicable)
        $dataValid = parent::validateData($data);

        do {
            // Invalidated by parent?
            if (!$dataValid)
                break;
            
            // Scripts detectdd?
            $dataValid = $this->detectScripts($data);
            if (!$dataValid) {
                $this->statusMessage = 'Validation failed. For security reasons, Javascript is not allowed.';
                break;
            }
        } while (false);

        return $dataValid;
    }
}