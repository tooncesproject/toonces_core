<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';

abstract class PageBuilder {
	
	var $elementArray = array();
	/*
	function getElementArray() {
		return $elementArray;
	}
	*/
	function buildPage() {
		// when making a child of PageBuilder, customize your class here
	
	}
	
}

?>