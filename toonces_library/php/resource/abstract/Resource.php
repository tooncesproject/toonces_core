<?php
/*
 *
 * Resource.php
 * Initial Commit: Paul Anderson, 4/11/2018
 * 
 * Abstract class providing common functionality for all Resource subclasses
 */

require_once LIBPATH.'php/toonces.php';

abstract class Resource {
    
    public $parameters;

    // PageViewReference is an iPageView-compliant object responsible for rendering the resource.
    /**
     * @var iPageView
     */
    public $pageViewReference;

    // Setters and getters for code hinting
    /**
     * @param iPageView $paramPageViewReference
     */
    public function setPageViewReference($paramPageViewReference) {
        $this->pageViewReference = $paramPageViewReference;
    }

    /**
     * @return iPageView
     */
    public function getPageViewReference() {
        return $this->pageViewReference;
    }

    public function __construct($pageView) {
        // All Resource subclasses have a reference to the PageView object upon instantiation. 
        $this->pageViewReference = $pageView;
        
        // Set the "parameters" property from the GET parameters, if applicable.
        $this->parameters = $_GET;
    }
}