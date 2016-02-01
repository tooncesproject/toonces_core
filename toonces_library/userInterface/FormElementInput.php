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
	public $hideInput = false;
	public $messageClass = 'input_message_notification';
	public $parentFormName;
	public $messageVarName;
	public $renderSignalVarName;
	public $hideSignalVarName;

	function __construct($paramName,$paramInputType,$paramParentFormName) {

		$this->name = $paramName;
		$this->inputType = $paramInputType;
		$this->parentFormName = $paramParentFormName;

		// Make receptive to utility session variables
		$this->messageVarName = $this->parentFormName.$this->name.'_inmsg';
		$this->renderSignalVarName = $this->parentFormName.$this->name.'_rsig';
		$this->hideSignalVarName = $this->parentFormName.$this->name.'_hsig';

		// receive message if exists.
		if (isset($_SESSION[$this->messageVarName])) {
			$this->message = $_SESSION[$this->messageVarName];
			// Destroy the message
			unset($_SESSION[$this->messageVarName]);
		}

		// Receive render signal if exists
		if (isset($_SESSION[$this->renderSignalVarName])) {
			$this->renderInput = $_SESSION[$this->renderSignalVarName];
			// Destroy the signal
			unset($_SESSION[$this->renderSignalVarName]);
		}

		//receive hide signal if exists
		if (isset($_SESSION[$this->hideSignalVarName])) {
			$this->hideInput = $_SESSION[$this->hideSignalVarName];
			// Destroy the signal
			unset($_SESSION[$this->hideSignalVarName]);
		}


		// Check for post data.
		if (isset($_POST[$this->name])) {
			$this->postState = true;
			$this->postData = isset($_POST[$this->name]) ? $_POST[$this->name] : NULL;
		}

	}

	public function storeMessage($paramMessage) {
		echo 'message var name:'.$this->messageVarName.'<br>';
		$this->message = $paramMessage;
		$_SESSION[$this->messageVarName] = $this->message;
	}

	public function storeRenderSignal($paramRenderSignal) {
		$this->renderInput = $paramRenderSignal;
		$_SESSION[$this->renderSignalVarName] = $this->renderInput;
	}

	public function storeHideSignal($paramHideSignal) {
		$this->hideInput = $paramHideSignal;
		$_SESSION[$this->hideSignalVarName] = $this->hideInput;
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

		// If no hide input signal, generate the HTML.
		if ($this->hideInput == false) {
			if (isset($this->message)) {
				$messageHTML = '<div class="'.$this->messageClass.'">'.$this->message.'</div>';
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
//			$this->html = $this->html.$displayNameHTML.PHP_EOL;
 
			if ($this->renderInput == true)
				$this->html = $this->html.$displayNameHTML.'<input type="'.$this->inputType.'" name="'.$this->name.'" '.$classHTML.$sizeHTML.$formValueHTML.'>'.PHP_EOL;
		}

	}

}