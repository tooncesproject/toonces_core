<?php
/*
 * InteractionDelegate
 * Initial commit: Paul Anderson, 9/2/2016
 * 
 * This abstract class defines helper classes that handle POST data received
 * to the server via an InteractionElement subclass.
 * 
 */
require_once LIBPATH.'toonces.php';

abstract class InteractionDelegate // implements iInteractionDelegate
{

	// Variable to hold the input array
	public $inputArray;
	public $formName;
	public $interactionElement;
	
	public function __construct($formName,$interactionElement) {
		$this->formName = $formName;
		$this->interactionElement = $interactionElement;
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