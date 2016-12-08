<?php

include_once LIBPATH.'toonces.php';

abstract class PageBuilder {

	var $styleSheet;
	var $pageTitle;
	var $elementArray = array();
	var $toolbarElement;

	// hold a reference to the PageView object, so I can pass it to the sub elements:

	var $pageViewReference;

	function __construct($pageview) {
		$this->pageViewReference = $pageview;

		//Check to see if the user is logged in. If so, build a toolbar element.

		if ($this->pageViewReference->sessionManager->adminSessionActive == true) {
			switch ($this->pageViewReference->pageTypeId) {
				// blog root
				case 2:
					$blogToolbarElement = new BlogToolbarElement($this->pageViewReference);
					$this->toolbarElement = $blogToolbarElement;
					break;
				// blog post
				case 3:
					$blogPostToolbarElement = new BlogPostToolbarElement($this->pageViewReference);
					$this->toolbarElement = $blogPostToolbarElement;
					break;
				default:
					$defaultToolbarElement = new DefaultToolbarElement($this->pageViewReference);
					$this->toolbarElement = $defaultToolbarElement;

			}
		}
	}

	function buildPage() {
		// when making a child of PageBuilder, customize your class here

	}

}

?>