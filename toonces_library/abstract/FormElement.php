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
	
	// $responseState:
	// Holds an integer indicating the response state.
	// Standard states: 0 is failure, 1 is success, higher integers if
	// multiple possible non-fail or partial-success states.
	// should be NULL if a post hasn't been received.
	var $responseState;
	
	// $postState:
	// Holds a boolean indicating whether the form is in
	//  false - Display state, no post received yet - default value
	//  true - Response state - a post has been submitted
	public $postState = false;	

	// Array of FormElementInput objects
	var $inputArray = array();

	// uh i guess i'm supposed to be able to set the title of the submit button
	var $submitName = 'submit';

	public function __construct($pageView) {
		// __construct handles the basic responsibilities of the element:
			// interface compliance
			// Element subclass responsibilities
			// set default setting values
		
		$this->pageViewReference = $pageView;
		//	$this->htmlHeader = '<div class="form_element>';
		//	$this->htmlFooter = '</div>';
	}

	public function generateFormHTML() {
		// Utility function, not likely you'll need to override.
		// Iterate through input objects to generate HTML.
		$this->html = '<form method="post">';
		//var_dump($this->inputArray);
		foreach ($this->inputArray as $inputObject) {
			$this->html = $this->html.$inputObject->html.'<br>';
		}
		$this->html = $this->html.'<br><input type="submit" value="'.$this->submitName.'">';
		$this->html = $this->html.'</form>';
	}
	function buildInputArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.
		// Also, this function handles 
	}
	
	private function responseStateHandler($responseState) {
		// Responsible for directing the form-level response to the input.
	}
}
