<?php

class LoginFormElement extends FormElement implements iElement
{

	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;


	function buildInputArray() {
		// Custom instantiation of input objects here.
		$usernameInput = new FormElementInput('email', 'text',$this->formName);
		$this->inputArray['email'] = $usernameInput;
		$usernameInput->displayName = 'Email';
		$usernameInput->size = 50;
		$usernameInput->setupForm();


		$pswInput = new FormElementInput('psw', 'password',$this->formName);

		$pswInput->displayName = 'Password';
		$pswInput->size = 50;
		$pswInput->setupForm();

		$this->inputArray['psw'] = $pswInput;

		$submitInput = new FormElementInput('submit','submit',$this->formName);
		$submitInput->formValue = $this->submitName;
		$submitInput->setupForm();

		$this->inputArray['submit'] = $submitInput;

	}

	function responseStateHandler($paramResponseState) {

		if ($paramResponseState == 0) {
			$this->generateFormHTML();
			$this->storeMessage('ACCESS DENIED. GO AWAY. Or try again.');

		}


		$this->send303();


	}


	function elementAction() {

		$loginSuccess = false;

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
				$this->inputArray['email']->storeMessage('Please enter an email address.');
				$doAttemptLogin = false;
			}
			if (empty($password)) {
				$this->responseState = 0;
				$this->inputArray['psw']->storeMessage('Please enter a password.');
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
		$this->htmlHeader = '<div class="form_element">';
		$this->htmlFooter = '</div>';
		$this->formName = 'loginForm';

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