<?php
/*
 * iFieldValidator
 * Initial commit: Paul Anderson, 4/13/2018
 * 
 * Interface for duck-typing field validator classes.
 * 
*/

require_once LIBPATH.'php/toonces.php';

interface iFieldValidator {
    public function validateData($data);
}