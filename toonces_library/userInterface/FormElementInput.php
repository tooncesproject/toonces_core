<?php
/*
 * FormElementInput
 * Initial commit: Paul Anderson 1/25/16
 * 
 * This class holds inputs to be handled by the FormElement class
 * The responsibilities of the inputs are:
 * 		Generate html for the input within the form
 * 		Gather the postdata from the input
 * 		Set the type of the input
 * 		Display input-level responses
 * 
 */

class FormElementInput
{
	public $name;
	public $inputType;
	public $size;
	public $postState = false;
	public $html;
	public $cssClass;
	public $message;
	public $postData;
	public $formValue;
	public $displayName;
	public $renderInput = true;
	public $messageClass = 'input_message_notification';
	public $parentFormName;
	public $messageVarName;
	
	function __construct($paramName,$paramInputType,$paramParentFormName) {

		$this->name = $paramName;
		$this->inputType = $paramInputType;
		$this->parentFormName = $paramParentFormName;
		
		$this->messageVarName = $this->parentFormName.$this->name.'_inmsg';

		if (isset($_SESSION[$this->messageVarName])) {
			$this->message = $_SESSION[$this->messageVarName];
		}
		// Check for post data.
		if (isset($_POST[$this->name])) {
			$this->postState = true;
			$this->postData = isset($_POST[$this->name]) ? $_POST[$this->name] : NULL;
		}

	}

	public function storeMessage($paramMessage) {
		$this->message = $paramMessage;
		$_SESSION[$this->messageVarName] = $this->message;
	}
	
	public function setupForm() {

		// Generate form.
		$this->generateForm($this->renderInput,$this->message,$this->messageClass);
	}

	// $message is a string to be displayed, optionally
	// $renderInput is a boolean determining whether to create an input  
	// $messageClass is the CSS class of the message, defaults to form_message_notification
	public function generateForm($renderInput, $message = NULL, $messageClass = NULL) {

		$this->html = '';
		$classHTML = '';
		$messageHTML = '';
		$formHTML = '';
		$displayNameHTML = '';
		$sizeHTML = '';
		$formValueHTML = '';

			
		if (isset($this->message)) {
			$messageHTML = '<div class="'.$this->messageClass.'">'.$this->message.'</div>';
			// Destroy the message
			unset($_SESSION[$this->messageVarName]);
		}
		
		if(isset($this->displayName))
			$displayNameHTML = '<div class="input_display_name">'.$this->displayName.'</div>';

		if (isset($this->cssClass)) 
			$classHTML = ' class="'.$this->cssClass.'"';

		if (isset($this->size))
			$sizeHTML = ' size="'.$this->size.'"';
		
		if (isset($this->formValue))
			$formValueHTML = ' value="'.$this->formValue.'"';
		
		$this->html = $this->html.$messageHTML.PHP_EOL;
		$this->html = $this->html.$displayNameHTML.PHP_EOL;
 
		//if ($this->renderInput)
			$this->html = $this->html.'<input type="'.$this->inputType.'" name="'.$this->name.'" '.$classHTML.$sizeHTML.$formValueHTML.'>'.PHP_EOL;
		
	}

}