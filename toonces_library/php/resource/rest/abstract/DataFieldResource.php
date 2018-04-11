<?php

/*
 * DataFieldResource.php
 * Initial commit: Paul Anderson, 4/10/2018
 * 
 * A DataResource subclasss that defines a typed data field.
 * 
*/

require_once LIBPATH.'php/toonces.php';

abstract class DataFieldResource extends DataResource implements iResource {

    public $data;
    public $allowNull = false;
    
    public function validateData($data) {
        // 
    }
    
    public function getResource() {
        // In this context, simply returns data
        return $this->data;
    }
    
    public function putResource($data) {
        // Setter method for the field's data
    }
    
    public function postResource($data) {
        // Simply duplicates the functionality of putResource
        $this->putResource($data);
    }
    
}
