<?php
/*
 * FieldValidator.php
 * Initial commit: Paul Anderson, 4/13/2018
 * 
 * Provides common functionality for FieldValidator classes.
 * 
*/

abstract class FieldValidator {
    public $allowNull;

    /**
     * FieldValidator constructor.
     * @param bool $paramAllowNull
     */
    public function __construct($paramAllowNull = false) {
        $this->allowNull = $paramAllowNull;
    }
}
