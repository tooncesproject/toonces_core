<?php
/*
 *
 * Resource.php
 * Initial Commit: Paul Anderson, 4/11/2018
 * 
 * Abstract class providing common functionality for all Resource subclasses
 */

require_once LIBPATH.'php/toonces.php';

abstract class Resource implements iResource {

    public $resourceId;

    public function setResourceId($paramResourceId) {
        $this->resourceId = $paramResourceId;
    }

    public function getResourceId() {
        return $this->resourceId;
    }

}