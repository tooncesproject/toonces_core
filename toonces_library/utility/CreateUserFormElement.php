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
	private $isAdmin;

	public function __construct($pageView) {
		
		$this->pageViewReference = $pageView;
		$this->success = 0;
		$this->htmlHeader = '<div class="form_element">';
		$this->htmlFooter = '</div>';
		
		//Empty strings to hold post response
		$emailResponseMsg = '';
		$passwordResponseMsg = '';
		$firstNameResponseMsg = '';
		$lastNameResponseMsg = '';
		$nicknameResponseMsg = '';
		
		// Was there a post?
		if ($this->checkPost() == 1) {
		$userManager = new UserManager;
		
			$responseArray = $userManager->createUser
			(
				 $this->email
				,$this->password
				,$this->firstName
				,$this->lastName
				,$this->nickname
				,$this->isAdmin
			);
			
			$emailResponseMsg = $responseArray['email']['responseMessage'].'<br>';
			$passwordResponseMsg = $responseArray['password']['responseMessage'].'<br>';
			$firstNameResponseMsg = $responseArray['firstName']['responseMessage'].'<br>';
			$lastNameResponseMsg = $responseArray['lastName']['responseMessage'].'<br>';
			$nickResponseMsg = $responseArray['nickname']['responseMessage'].'<br>';
			
			// Check for success
			$this->success = 1;
			foreach($responseArray as $response) {
				if ($response['responseState'] != 1) {
					$this->success = 0;
				}

			}
		}
		
		// If successful, build response page.
		// Otherwise, build form HTML (with messages, if applicable).
		if ($this->success == 1) {
			if ($this->isAdmin == 1) {
				$adminMsg = 'Yes';
			} else {
				$adminMsg = 'No';
			}
			
			$this->html = sprintf($this->successHTML(),$this->email,$this->firstName,$this->lastName,$this->nickname, $adminMsg);
		} else {
			$this->html = sprintf($this->formHTML(),$emailResponseMsg,$firstNameResponseMsg,$lastNameResponseMsg,$nicknameResponseMsg,$passwordResponseMsg);
		}
		
	}

	function formHTML() {
		
		$html = <<<HTML
		<div class="utility_block">
		<h2>Create New User </h2>
           <form id="login" method="post">
                Email:<br>
				%s
                <input type="text" name="uc_email" size="50">
                <br>
				First Name:<br>
				%s
				<input type="text" name="uc_firstname" size="50">
                <br>
				Last Name:<br>
				%s
				<input type="text" name="uc_lastname" size="50">
                <br>
				Nickname:<br>
				%s
				<input type="text" name="uc_nickname" size="50">
                <br>
                Password:<br>
				%s
                <input type="password" name="uc_password" size="50">
				<br>
				<input type="checkbox" name="uc_isadmin" value="1"> Grant Admin Privileges
                <br>
                <br>
                <input type="submit" value="Woo!"/>
            </form>
			<br>
			<p><a href="/admin/useradmin/">Back to User Administration</a></p>
		</div>
HTML;
	
		return $html;
	}
	
	function successHTML() {
		$html = <<<HTML
		<div class="utility_block">
			<h2>Success!</h3>
			<p>Email: %s</p>
			<p>First Name: %s</p>
			<p>Last Name: %s</p>
			<p>Nickname: %s</p>
			<p>Has Admin Privileges: %s</p>
		</div>
HTML;

		return $html;
	}
	
	function checkPost() {
		
		$postStatus = 0;
		//Default isAdmin to false
		$this->isAdmin = 0;

		// Receive postdata
		if (isset($_POST['uc_email'])) {
			$this->email = filter_input(INPUT_POST, 'uc_email', FILTER_SANITIZE_EMAIL);
			$postStatus = 1;
		}
		if (isset($_POST['uc_firstname'])) {
			$this->firstName = filter_input(INPUT_POST, 'uc_firstname', FILTER_SANITIZE_STRING);
			$postStatus = 1;
		}
		if (isset($_POST['uc_lastname'])) {
			$this->lastName = filter_input(INPUT_POST, 'uc_lastname', FILTER_SANITIZE_STRING);
			$postStatus = 1;
		}
		if (isset($_POST['uc_nickname'])) {
			$this->nickname = filter_input(INPUT_POST, 'uc_nickname', FILTER_SANITIZE_STRING);
			$postStatus = 1;
		}
		if (isset($_POST['uc_password'])) {
			$this->password = filter_input(INPUT_POST, 'uc_password', FILTER_SANITIZE_STRING);
			$postStatus = 1;
		}
		if (isset($_POST['uc_isadmin'])) {
			$this->isAdmin = $_POST['uc_isadmin'];
			$postStatus = 1;
		}
		
		return $postStatus;	 
	}
	
}