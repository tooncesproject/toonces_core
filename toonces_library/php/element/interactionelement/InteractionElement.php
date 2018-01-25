<?php
/*
	The FormElement renders HTML and handles response for html forms and POSTdata.
	Its responsibilities are:
		Iterate through an array of FormElementInput objects to render HTML
		Decide what to do with the input

*/

require_once LIBPATH.'php/toonces.php';

class InteractionElement extends Element implements iResource
{

	// $postState:
	// Holds a boolean indicating whether the form is in
	//  false - Display state, no post received yet - default value
	//  true - Response state - a post has been submitted
	public $postState = false;


	// submitName holds a text string that will appear in the submit button.
	public $formName;
	public $messageVarName;

	// This variable holds the custom InteractionDelegate object that manages input and output.
	public $interactionDelegate;


	public function generateFormHTML() {
		// Utility function, not likely you'll need to override.
		// Iterate through input objects to generate HTML.
		$formNameHTML = '';
		$messageHTML = '';

		if (isset($this->formName) == false)
			throw new Exception('Form name must be set.');


		$this->messageVarName = $this->interactionDelegate->messageVarName;
		if (isset($_SESSION[$this->messageVarName]))
			$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';

		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);


		$formNameHTML = 'name="'.$this->formName.'"';
		$formIdHTML = 'id="'.$this->formName.'"';

		$this->html = $messageHTML.PHP_EOL;

		$this->html = $this->html.'<form method="post" '.$formNameHTML.$formIdHTML.'>';
		foreach ($this->interactionDelegate->inputArray as $inputObject) {

			// Here:
			// If the input object is not a textarea,  go ahead and add it.
			if ($inputObject->hideInput == false and get_class($inputObject) != 'TextareaFormInput')
				$this->html = $this->html.$inputObject->getResource(true);
		}

		$this->html = $this->html.'</form>';

		// If there are any textarea input objects, add them here, outside the form.
		foreach ($this->interactionDelegate->inputArray as $inputObject) {
			if ($inputObject->hideInput == false and get_class($inputObject) == 'TextareaFormInput')
				$this->html = $this->html.$inputObject->getResource(true);
		}

	}


	public function getResource() {

		// Throw an exception of an InteractionDelegate object has not been assigned by runtime.
		if (!isset($this->interactionDelegate))
			throw new Exception('InteractionElement error: InteractionDelegate must be assigned to this object before runtime.');

		// If the array of input objects is empty at load time, you suck.
		if (empty($this->interactionDelegate->inputArray))
			throw new Exception('InteractionElement error: Array of FormInput objects must be populated!');

		// Iterate through input objects to see if any received a POST
		foreach ($this->interactionDelegate->inputArray as $input) {
			if ($input->postState)
				$this->postState = true;
		}

		// If the FormInput objects in the array detected POST data, defer action to the delegate.
		// Otherwise, render the form.
		if ($this->postState) {
			$this->interactionDelegate->processFormData();
		} else {
			$this->generateFormHTML();
		}

		// If HTML header and footer have not been set externally, default to standard style.
		if (!isset($this->htmlHeader))
			$this->htmlHeader = '<div class="copy_block">';

		if (!isset($this->htmlFooter))
			$this->htmlFooter = '</div>';

		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;

		return $this->html;
	}


}
