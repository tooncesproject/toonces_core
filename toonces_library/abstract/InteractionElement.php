<?php
/*
	The FormElement renders HTML and handles response for html forms and POSTdata.
	Its responsibilities are:
		Iterate through an array of FormElementInput objects to render HTML
		Decide what to do with the input

*/

require_once LIBPATH.'toonces.php';

abstract class InteractionElement extends Element
{

	// $postState:
	// Holds a boolean indicating whether the form is in
	//  false - Display state, no post received yet - default value
	//  true - Response state - a post has been submitted
	public $postState = false;

	// Array of FormElementInput objects
	var $inputArray = array();

	// submitName holds a text string that will appear in the submit button.
	public $submitName;
	public $formName;
	public $messageVarName;
	
	public function buildInputArray() {
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.
	}
	

	public function generateFormHTML() {
		// Utility function, not likely you'll need to override.
		// Iterate through input objects to generate HTML.
		$formNameHTML = '';
		$messageHTML = '';

		if (isset($this->formName) == false)
			throw new Exception('Form name must be set.');

		$this->messageVarName = $this->formName.'_msg';
		if (isset($_SESSION[$this->messageVarName]))
			$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';

		$formNameHTML = 'name="'.$this->formName.'"';

		$this->html = $messageHTML.PHP_EOL;

		$this->html = $this->html.'<form method="post" '.$formNameHTML.'>';
		foreach ($this->inputArray as $inputObject) {
			
			// Here:
			// If the input object is not a textarea,  go ahead and add it.
			if ($inputObject->hideInput == false and $inputObject->inputType != 'textarea')	
				$this->html = $this->html.$inputObject->html;
		}

		$this->html = $this->html.'</form>';
		
		// If there are any textarea input objects, add them here?
		foreach ($this->inputArray as $inputObject) {
			if ($inputObject->hideInput == false and $inputObject->inputType == 'textarea')
				$this->html = $this->html.$inputObject->html;
		}

		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);
	}


	public function getHTML() {
			
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;
		}
	
		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;
	
		return $this->html;
	}


}
