<?php

require_once ROOTPATH.'/toonces.php';

class UserManager
{
	var $conn;
	
	function createUser
	(
			 $email
			,$password
			,$firstName
			,$lastName
			,$nickname
			,$isAdmin
	)
	{
		$responseArray = array();
		$inputIsValid = 0;
		
		// $createUserStatus =
		// validate email and check for existence
		$email = filter_var($email,FILTER_VALIDATE_EMAIL);
		if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
			$responseArray['email']['responseState'] = 0;
			$responseArray['email']['responseMessage'] = "Please enter a valid email address.";
			$inputIsValid = 0;
		} else if ($this->checkUserEmailExistence($email) == 1) {
			$responseArray['email']['responseState'] = 0;
			$responseArray['email']['responseMessage'] = "An account with that email address already exists.";
			$inputIsValid = 0;
		} else {
			$responseArray['email']['responseState'] = 1;
			$responseArray['email']['responseMessage'] = '';
		}

		// Validate password
		if (strlen($password) < 8) {
			$responseArray['password']['responseState'] = 0;
			$responseArray['password']['responseMessage'] = "Please choose a password with at least 8 characters.";
			$inputIsValid = 0;
		} else {
			$responseArray['password']['responseState'] = 1;
			$responseArray['password']['responseMessage'] = '';
			$inputIsValid = 1;
		}
		
		// Validate First Name
		if (strlen($firstName) < 1) {
			$responseArray['firstName']['responseState'] = 0;
			$responseArray['firstName']['responseMessage'] = "Please add a first name..";
			$inputIsValid = 0;
		} else {
			$responseArray['firstName']['responseState'] = 1;
			$responseArray['firstName']['responseMessage'] = '';
			$inputIsValid = 1;
		}
		
		// Validate last name
		if (strlen($lastName) < 2) {
			$responseArray['lastName']['responseState'] = 0;
			$responseArray['lastName']['responseMessage'] = "Please choose a Last Name with at least 2 characters.";
			$inputIsValid = 0;
		} else {
			$responseArray['lastName']['responseState'] = 1;
			$responseArray['lastName']['responseMessage'] = '';
			$inputIsValid = 1;
		}
		
		// Validate nickname
		if (strlen($nickname) < 2) {
			$responseArray['nickname']['responseState'] = 0;
			$responseArray['nickname']['responseMessage'] = "Please enter a nickname.";
			$inputIsValid = 0;
		} else {
			$responseArray['nickname']['responseState'] = 1;
			$responseArray['nickname']['responseMessage'] = '';
			$inputIsValid = 1;
		}
		
		// If input is valid, create the user.
		if ($inputIsValid == 1) {
			// Create the random salt
			$salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			
			// Create salted password
			$password = hash('sha512', $password . $salt);
			
			// Insert record
			$sql = <<<SQL
			INSERT INTO toonces.users
			(
				 email
				,nickname
				,firstname
				,lastname
				,password
				,salt
				,is_admin
			) VALUES (
				 '%s'
				,'%s'
				,'%s'
				,'%s'
				,'%s'
				,'%s'
				,%s
			)
SQL;
			
			$sql = sprintf($sql,$email,$nickname,$firstName,$lastName,$password,$salt,$isAdmin);
			$this->conn->query($sql);
		}
		
		return $responseArray;
	
	}
	
	function checkUserEmailExistence($email) {
		
		$emailExists = 0;
		
		$sql = "SELECT user_id FROM toonces.users WHERE LOWER(email) = LOWER('%s')";
		$sql = sprintf($sql,$email);
		
		$this->conn = UniversalConnect::doConnect();
		$result = $this->conn->query($sql);
		
		foreach ($result as $row) {
			$emailExists = 1;
		}
		
		return $emailExists;
		
	}
	
}