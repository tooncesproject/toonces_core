<?php
/*
 * iFormInput
 * Initial commit: Paul Anderson, 9/2/2016
 * 
 * Interface defining FormInput objects (i.e., FormElementInput
 * 
 */

interface iFormInput
{
	public $name;
	public $inputType;
	public $size;
	public $postState;
	public $html;
	public $cssClass;
	public $message;
	public $postData;
	public $formValue;
	public $displayName;
	public $renderInput;
	public $hideInput;
	public $messageClass = 'input_message_notification';
	public $parentFormName;
	public $messageVarName;
	public $renderSignalVarName;
	public $hideSignalVarName;
	public $valueVarName;
	
	// Constructor function accepting basic parameters of object
	function __construct($paramName,$paramInputType,$paramParentFormName) {
		
	}
	
	// Stores a response message to the session (so the page can display a message after POST, redirect and GET) 
	public function storeMessage($paramMessage) {

	}
	
	// Stores a signal in the session whether or not to render the input
	public function storeRenderSignal($paramRenderSignal) {

	}
	
	// Stores a signal in the session whether or not to hide the input
	// redundant?
	public function storeHideSignal($paramHideSignal) {

	}
	
	// Stores a string in the session so the form can display a value previously input by the user 
	public function storeValue($paramFormValue) {
		
	}

	// Can probably deprecate this. All it does is call the generateForm method. 
	public function setupForm() {

	}
	
	// Generates the input HTML.
	// $message is a string to be displayed, optionally
	// $renderInput is a boolean determining whether to create an input
	// $messageClass is the CSS class of the message, defaults to form_message_notification
	public function generateForm($renderInput, $message = NULL, $messageClass = NULL) {

	}
}