<?php
/*
	The FormElement renders HTML and handles response for html forms and POSTdata.
	Its responsibilities are:
		Iterate through an array of FormElementInput objects to render HTML
		Decide what to do with the input

*/

require_once ROOTPATH.'/toonces.php';

abstract class FormElement extends Element
{
	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;
	
	// Abstract variables:
	
	// Binary success/fail state (doesn't have to be binary)
	private $success;
	
	// Array of FormElementInput objects
	private $formArray = array();
	
	// Settings
	private $responseType;
	private $htmlTag;
	private $cssClass;
	
	// Default settings
	private $responseTypeDefault;
	private $htmlTagDefault;
	private $cssClassDefault;
	
	public function __construct($pageView) {
		// __construct handles the basic responsibilities of the element:
			// interface compliance
			// Element subclass responsibilities
			// set default setting values
		
		$this->pageViewReference = $pageView;
		//	$this->htmlHeader = '<div class="form_element>';
		//	$this->htmlFooter = '</div>';
	}

	function buildFormArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.
		// Also, this function handles 
	}
	
	function responseStateHandler($responseState) {
		
	}
	
	function objectSettings() {
		// Customizable method to override default settings if desired.
		
	}
	
}
