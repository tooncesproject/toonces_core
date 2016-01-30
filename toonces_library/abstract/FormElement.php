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
	public $submitName;
	
	public $formName;
	
	public $messageVarName;

	public function __construct($pageView) {
		// __construct handles the basic responsibilities of the element:
			// interface compliance
			// Element subclass responsibilities
			// set default setting values

		// required stuff
		$this->pageViewReference = $pageView;
		$this->objectSetup();
		$this->elementAction();
	}

	public function generateFormHTML() {
		// Utility function, not likely you'll need to override.
		// Iterate through input objects to generate HTML.
		$formNameHTML = '';
		$messageHTML = '';
		/*
		if (isset($this->formName))
			
		*/
		if (isset($this->formName) == false)
			throw new Exception('Form name must be set.');

		$this->messageVarName = $this->formName.'_msg';
		if (isset($_SESSION[$this->messageVarName]))
			$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';
		
		$formNameHTML = 'name="'.$this->formName.'"';
		
		$this->html = $messageHTML.PHP_EOL;

		$this->html = $this->html.'<form method="post" '.$formNameHTML.'>';
		foreach ($this->inputArray as $inputObject) {
			$this->html = $this->html.$inputObject->html.'<br>';
		}

		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);
	}
	public function buildInputArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.
	}
	
	public function responseStateHandler($responseState) {
		// Responsible for directing the form-level response to the input.
		// By default, just sends a 303 GET header.
		$this->send303();
	}

	function send303($paramURI = '') {
		
		// By default, URI is current page.
		$uri = $_SERVER[REQUEST_URI];
		if (empty($paramURI) == false)
			$uri = $paramURI;
			
		$link = "http://$_SERVER[HTTP_HOST]$uri";
		header("HTTP/1.1 303 See Other");
		header('Location: '.$link);
	}
	
	public function objectSetup() {
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
	
		// Instantiate input objects
		$this->buildInputArray();		
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;

		}

	}

	function elementAction() {
		// Custom behavior setup lives here.
	
	}
}
