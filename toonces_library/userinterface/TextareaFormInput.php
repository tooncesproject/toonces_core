<?php
/*
 * FormInput
 * Initial commit: Paul Anderson 9/5/16
 *
 * Versioned from FormElementInput to simplify implementation.
 * This class holds inputs to be handled by the FormElement class
 * The responsibilities of the inputs are:
 * 		Generate html for the input within the form
 * 		Gather the postdata from the input
 * 		Set the type of the input
 * 		Display input-level responses
 *
 */

class TextareaFormInput extends FormInput implements iFormInput
{

	public $rows;
	public $cols;

	function __construct($paramName,$paramParentFormName) {

		$this->name = $paramName;
		$this->inputType = 'hidden';
		$this->parentFormName = $paramParentFormName;

		// Make receptive to utility session variables
		$this->messageVarName = $this->parentFormName.$this->name.'_inmsg';
		$this->renderSignalVarName = $this->parentFormName.$this->name.'_rsig';
		$this->hideSignalVarName = $this->parentFormName.$this->name.'_hsig';
		$this->valueVarName = $this->parentFormName.$this->name.'_vsig';

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

		//receive value signal if exists
		if (isset($_SESSION[$this->valueVarName])) {
			$this->formValue = $_SESSION[$this->valueVarName];
			// Destroy the signal
			unset($_SESSION[$this->valueVarName]);
		}

		// Check for post data.
		if (isset($_POST[$this->name])) {
			$this->postState = true;
			$this->postData = isset($_POST[$this->name]) ? $_POST[$this->name] : NULL;
		}

	}

	/*	
	public function storeMessage($paramMessage) {
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

	public function storeValue($paramFormValue) {
		$this->formValue = $paramFormValue;
		$_SESSION[$this->valueVarName] = $paramFormValue;
	}

	*/
	// $message is a string to be displayed, optionally
	// $renderInput is a boolean determining whether to create an input
	// $messageClass is the CSS class of the message, defaults to form_message_notification
	public function getHTML($renderInput, $message = NULL, $messageClass = NULL) {

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


			if ($this->renderInput == true)
				$this->html = '<textarea name="'.$this->name.'" rows="'.$this->rows.'" cols="'.$this->cols.'" form="'.$this->parentFormName.'">'.PHP_EOL.$this->formValue.'</textarea>'.PHP_EOL;
		}

		return $this->html;

	}

	
}