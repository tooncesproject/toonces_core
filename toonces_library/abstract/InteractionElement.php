<?php
/*
	The FormElement renders HTML and handles response for html forms and POSTdata.
	Its responsibilities are:
		Iterate through an array of FormElementInput objects to render HTML
		Decide what to do with the input

*/

require_once LIBPATH.'toonces.php';

abstract class InteractionElement extends Element implements iElement
{

	// $postState:
	// Holds a boolean indicating whether the form is in
	//  false - Display state, no post received yet - default value
	//  true - Response state - a post has been submitted
	public $postState = false;

	// Array of FormElementInput objects
	// Paul note: gonna try having delegate object hold array
	// var $inputArray = array();

	// submitName holds a text string that will appear in the submit button.
	public $submitName;
	public $formName;
	public $messageVarName;
	
	// This is the 
	public $interactionDelegate;


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
		foreach ($this->interactionDelegate->inputArray as $inputObject) {
			
			// Here:
			// If the input object is not a textarea,  go ahead and add it.
			if ($inputObject->hideInput == false and get_class($inputObject != 'TextareaFormInput'))	
				$this->html = $this->html.$inputObject->getHTML;
		}

		$this->html = $this->html.'</form>';
		
		// If there are any textarea input objects, add them here, outside the form.
		foreach ($this->interactionDelegate->inputArray as $inputObject) {
			if ($inputObject->hideInput == false and get_class($inputObject == 'TextareaFormInput'))
				$this->html = $this->html.$inputObject->getHTML;
		}

		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);
	}


	public function getHTML() {
			
		// If the array of input objects is empty at load time, you suck.
		if (empty($this->interactionDelegate->inputArray))
			throw new Exception('InteractionElement error: Array of FormInput objects must be populated!');
		
		// Iterate through input objects to see if any received a POST
		foreach ($this->interactionDelegate->inputArray as $input) {
			if ($input->postState)
				$this->postState = true;
		}

		// If the FormInput objects in the array did not detect POST data, render the form.
		// Otherwise, defer action to the InteractionDelegate object.
		if ($this->postState) {
			
		}
		
		
		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;
	
		return $this->html;
	}


}
