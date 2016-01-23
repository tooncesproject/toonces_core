<?php

include_once ROOTPATH.'/toonces.php';

abstract class PageBuilder {
	
	var $styleSheet;
	var $pageTitle;
	var $elementArray = array();
	
	// hold a reference to the PageView object, so I can pass it to the sub elements:
	
	var $pageViewReference;
	
	/*
	function getElementArray() {
		return $elementArray;
	}
	*/
	
	//page view reference setter method
	
	
	function buildPage($pageView) {
		// when making a child of PageBuilder, customize your class here
	
	}
	
}

?>