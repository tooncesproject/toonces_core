<?php

class LoginFormElement extends FormElement implements iElement
{
	
	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;

	public function __construct($pageView) {

			$this->pageViewReference = $pageView;
			$this->objectSetup();
			$this->elementAction();

		}


	function formHTML() {
		
		$html = <<<HTML
            <form id="login" method="post">
                Email:<br>
                <input type="text" name="username" size="50">
                <br>
                <br>
                Password:<br>
                <input type="password" name="psw" size="50">
                <br>
                <br>
                <input type="submit" value="Shit yeah!"/>
            </form>
HTML;
	
		return $html;
	}

	function buildInputArray() {
		// Custom instantiation of input objects here.
		$usernameInput = new FormElementInput('email', 'text','Email',50);;
		$this->inputArray['email'] = $usernameInput;

		$pswInput = new FormElementInput('psw', 'password','Password',50);
		$this->inputArray['psw'] = $pswInput;
		
		//$submitInput = new FormElementInput('submit','submit');
		$submitInput = new FormElementInput('submit','submit',null,null,null,$this->submitName);

		
		$this->inputArray['submit'] = $submitInput;

	}
	
	function responseStateHandler($paramResponseState) {
		
		if ($paramResponseState == 0) {
			$this->generateFormHTML();
		 	$message = '<div class="form_message_notifiacation"><p>ACCESS DENIED. GO AWAY. Or try again.</p></div>';
		 	$this->html = $message.PHP_EOL.$this->html;
		}

			
		$this->send303();


	}


	function elementAction() {

		$loginSuccess = false;

		// Set the submit value as "Shit Yeah!"

		// if no post, render the form.
		// Otherwise, process the input.
		if ($this->postState == false) {
			$this->generateFormHTML();
		} else {

			// By default, attempt login
			$doAttemptLogin = true;
			
			// Gather POST data
			$emailInput = $this->inputArray['email'];
			$email = $emailInput->postData;
			
			$passwordInput = $this->inputArray['psw'];
			$password = $passwordInput->postData;
			
			// If email or password input is blank, response state is 0.
			// Display warning message.
			if (empty($email)) {
				$this->responseState = 0;
				$this->inputArray['email']->message = 'Please enter an email address.';
				$doAttemptLogin = false;
			}
			if (empty($password)) {
				$this->responseState = 0;
				$this->inputArray['psw']->message = 'Please enter a password.';
				$doAttemptLogin = false;
			}
			
			if ($doAttemptLogin == true) {
				$loginSuccess = $this->pageViewReference->sessionManager->login($email,$password);
			}
			
			// If login was not successful, display the login fail message.
			//if (isset($this->pageViewReference->loginSuccess) and $this->pageViewReference->loginSuccess == 0) {
			if ($loginSuccess == false) {
				$this->responseState = 0;
			} else {
				$this->responseState = 1;
			}

			$this->responseStateHandler($this->responseState);

		}

	}

	public function objectSetup() {
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
	
		$this->submitName = 'Shit Yeah!';
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;
	
		}
	

	}
}