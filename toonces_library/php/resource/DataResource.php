<?php
/*
 * DataResource.php
 * Initial Commit: Paul Anderson, 2/20/2018
 * iResource implementation holding raw data as a PHP array
 *
 */

include_once LIBPATH.'php/toonces.php';

class JSONResource implements iResource
{
    var $dataObjects = array();
    var $elementsCount = 0;
    
    
    public function addElement ($element) {
        
        array_push($this->dataObjects,$element);
        $this->elementsCount++;
        
    }
    
    // execution method
    public function getResource() {
        
        return $this->dataObjects;
        
    }
}