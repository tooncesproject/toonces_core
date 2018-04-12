<?php
/*
 * Resource.php
 * Initial Commit: Paul Anderson, 4/11/2018
 * 
 * Abstract class providing common functionality for all Resource subclasses
 */

require_once LIBPATH.'php/toonces.php';

abstract class Resource {

    // PageViewReference is an iPageView-compliant object responsible for rendering the resource.
    protected $pageViewReference;
    
    protected function __construct($pageView) {
        // All Resource subclasses have a reference to the PageView object upon instantiation. 
        $this->pageViewReference = $pageView;
    }
}