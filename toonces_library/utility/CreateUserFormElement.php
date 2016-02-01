<?php

class CreateUserFormElement extends FormElement implements iElement
{

	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;
	// private $success;
	private $email;
	private $firstName;
	private $lastName;
	private $nickname;
	private $password;
	private $adminCreated;

	function buildInputArray() {
	// Custom instantiation of input objects here.

		// email
		$emailInput = new FormElementInput('email', 'text', $this->formName);
		$this->inputArray['email'] = $emailInput;
		$emailInput->displayName = 'Email';
		$emailInput->size = 50;
		$emailInput->setupForm();

		// first name
		$firstNameInput = new FormElementInput('firstName', 'text', $this->formName);
		$this->inputArray['firstName'] = $firstNameInput;
		$firstNameInput->displayName = 'First Name';
		$firstNameInput->size = 50;
		$firstNameInput->setupForm();

		// last name
		$lastNameInput = new FormElementInput('lastName', 'text', $this->formName);
		$this->inputArray['lastName'] = $lastNameInput;
		$lastNameInput->displayName = 'Last Name';
		$lastNameInput->size = 50;
		$lastNameInput->setupForm();

		// nickname
		$nicknameInput = new FormElementInput('nickname', 'text', $this->formName);
		$this->inputArray['nickname'] = $nicknameInput;
		$nicknameInput->displayName = 'Nickname';
		$nicknameInput->size = 50;
		$nicknameInput->setupForm();

		// password
		$passwordInput = new FormElementInput('password', 'password', $this->formName);
		$this->inputArray['password'] = $passwordInput;
		$passwordInput->displayName = 'Password';
		$passwordInput->size = 50;
		$passwordInput->setupForm();

		// confirm password
		$confirmPasswordInput = new FormElementInput('confirmPassword', 'password', $this->formName);
		$this->inputArray['confirmPassword'] = $confirmPasswordInput;
		$confirmPasswordInput->displayName = 'Confirm Password';
		$confirmPasswordInput->size = 50;
		$confirmPasswordInput->setupForm();

		// Is admin checkbox
		$grantAdminInput = new FormElementInput('grantAdmin', 'checkbox', $this->formName);
		$this->inputArray['grantAdmin'] = $grantAdminInput;
		$grantAdminInput->displayName = 'Grant Admin Privileges';
		$grantAdminInput->setupForm();

		//form submit
		$submitInput = new FormElementInput('submit','submit',$this->formName);
		$submitInput->formValue = $this->submitName;
		$submitInput->setupForm();
		$this->inputArray['submit'] = $submitInput;

	}

	function responseStateHandler($responseState) {

		// fail state
		if ($responseState == 0) {
			$this->generateFormHTML();
			$this->storeMessage('<p>Oops! That didn\'t work.</p>');
			$this->send303();

		// success
		} else if ($responseState == 1) {
			foreach ($this->inputArray as $input) {
				$inputName = $input->displayName.': ';
				$inputValue = $input->postData;
				$input->storeRenderSignal(false);

				// Display input data except password, confirm password and submit
				if ($input->name != 'password' and $input->name != 'confirmPassword' and $input->name != 'submit') {
					$input->storeMessage($inputName.$inputValue);
				} else {
					$input->storeHideSignal(true);
				}

				// In the case of the admin switch, change the input value to something readable.
				if ($input->name == 'grantAdmin') {
					$inputValue = ($inputValue === 'on') ? 'Yes' : 'No';
					$input->storeMessage($inputName.$inputValue);
				}

				$input->setupForm();
			}
			$this->generateFormHTML();
			$successMessage = <<<HTML
			<p>Hooray! You successfully created a user.</p>
			<p><a href="%s">Create Another User</a></p>
			<p><a href="%s">Back to User Administration</a></p>
HTML;
			$parentPageUrl = GrabParentPageURL::getURL($this->pageViewReference->pageId);
			$successMessage = sprintf($successMessage, $_SERVER['REQUEST_URI'],$parentPageUrl);

			$this->storeMessage($successMessage);

			$this->send303();

		}
	}


	function elementAction() {

		$success = 0;

		// if no post, render the form.
		// Otherwise, process the input.
		if ($this->postState == false) {
			$this->generateFormHTML();
		} else {

			// By default, attempt to create user.
			$doAttemptCreate = true;

			// Gather POST data
			// email
			$emailInput = $this->inputArray['email'];
			$email = filter_var($emailInput->postData,FILTER_SANITIZE_EMAIL);

			// First name
			$firstNameInput = $this->inputArray['firstName'];
			$firstName = filter_var($firstNameInput->postData, FILTER_SANITIZE_STRING);

			// Last name
			$lastNameInput = $this->inputArray['lastName'];
			$lastName = filter_var($lastNameInput->postData,FILTER_SANITIZE_STRING);

			// nickname
			$nicknameInput = $this->inputArray['nickname'];
			$nickname = filter_var($nicknameInput->postData,FILTER_SANITIZE_STRING);

			// password
			$passwordInput = $this->inputArray['password'];
			$password = $passwordInput->postData;

			// confirm password
			$confirmPasswordInput = $this->inputArray['confirmPassword'];
			$confirmPassword = $confirmPasswordInput->postData;

			// grant admin
			$grantAdminInput = $this->inputArray['grantAdmin'];
			$grantAdmin = isset($grantAdminInput->postData) ? 1 : 0;

			// input validation is handled by the UserManager object.
			$userManager = new UserManager;

			$responseArray = $userManager->createUser
			(
					 $email
					,$password
					,$confirmPassword
					,$firstName
					,$lastName
					,$nickname
					,$grantAdmin
			);

			// Check each response and update the FormInputElement objects accordingly.
			$success = 1;
			reset($responseArray);
			foreach($responseArray as $inputName => $response) {

				//$inputName = key($response);
				if ($response['responseState'] == 0) {
					$responseMesssage = isset($response['responseMessage']) ? $response['responseMessage'] : '';
					//$inputName = key($responseArray);
					$this->inputArray[$inputName]->storeMessage($responseMesssage);
					$success = 0;
				}
			}

			if ($success == 1)
				$this->adminCreated = $grantAdmin;

			$this->responseStateHandler($success);
		}
	}


	public function objectSetup() {

		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		$this->formName = 'createUserForm';

		$this->submitName = 'Create User';
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;

		}
	}

}