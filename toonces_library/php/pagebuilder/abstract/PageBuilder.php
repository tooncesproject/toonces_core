<?php

include_once LIBPATH.'php/toonces.php';

abstract class PageBuilder {

	var $styleSheet;
	var $pageTitle;
	var $elementArray = array();
	var $toolbarElement;

	// hold a reference to the PageView object, so I can pass it to the sub elements:

    /**
     * @var iPageView
     */
	var $pageViewReference;

	function __construct($pageview) {
		$this->pageViewReference = $pageview;

		//Check to see if the user is logged in. If so, build a toolbar element.

		if ($this->pageViewReference->checkAdminSession())
		    $this->makeToolbarElement();

	}

	function makeToolbarElement() {
	    // Default behavior is to instantiate the DefaultToolbarElement.
        // To customize a toolbar, override this method.
        $this->toolbarElement = new DefaultToolbarElement($this->pageViewReference);

    }

	function buildPage() {
		// when making a child of PageBuilder, customize your class here
	}

}
