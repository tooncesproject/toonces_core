<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';

abstract class PageBuilder {
	
	var $styleSheet;
	var $pageTitle;
	var $ElementArray = array();
	
	// hold a reference to the PageView object, so I can pass it to the sub elements:
	
	var $pageViewReference;
	
	/*
	function getElementArray() {
		return $elementArray;
	}
	*/
	
	//page view reference setter method
	
	
	function buildPage() {
		// when making a child of PageBuilder, customize your class here
	
	}
	
}

?>