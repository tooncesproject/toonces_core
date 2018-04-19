<?php
/*
 * InteractionDelegate
 * Initial commit: Paul Anderson, 9/2/2016
 * 
 * This abstract class defines helper classes that handle POST data received
 * to the server via an InteractionElement subclass.
 * 
 */
require_once LIBPATH.'php/toonces.php';

abstract class InteractionDelegate implements iInteractionDelegate
{

	public $inputArray; 			// Variable to hold the input array
	public $formName;				// Name of form - Required.
	public $interactionElement;		// InterationElement object - Required.
	public $submitName = 'Submit';	// Default text displayed on Submit button if implemented
	public $messageVarName;			// Name of variable holding a user feedback message in session.

	// To construct a delegate:
		// $formName is the name assigned to the form via InteractionElement
		// $interactionElement is the previously instantiated InteractionElement object
	public function __construct($formName,$interactionElement) {

		// Assign the name and InteractionElement object
		$this->formName = $formName;
		$this->interactionElement = $interactionElement;

		// Generate a variable name for the form message (feedback for user, stored in the session)
		$this->messageVarName = $this->formName.'_msg';
		//if (isset($_SESSION[$this->messageVarName]))
		//	$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';

	}

	// Method handling form data processing
	public function processFormData() {

	}

	// Stores a message in the session so that the app can give feedback after redirect 
	public function storeMessage($message) {
		$_SESSION[$this->messageVarName] = $message;
	}

	// Redirects user with HTTP 303 status to prevent duplicate form submissions.
	function send303($paramURI = '') {

		// By default, URI is current page.
		$uri = $_SERVER[REQUEST_URI];
		if (empty($paramURI) == false)
			$uri = $paramURI;

			$link = "http://$_SERVER[HTTP_HOST]$uri";
			header("HTTP/1.1 303 See Other");
			header('Location: '.$link);
	}

}