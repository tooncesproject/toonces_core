<?php

require_once LIBPATH.'php/toonces.php';

class UserManager
{
	var $conn;

	function __construct($paramSQLConn) {
        $this->conn = $paramSQLConn;	    
	}

	function createUser
	(
			 $email
			,$password
			,$confirmPassword
			,$firstName
			,$lastName
			,$nickname
			,$isAdmin
	)
	{
		$responseArray = array();
		$inputIsValid = 0;

		// validate email and check for existence
		$email = filter_var($email,FILTER_VALIDATE_EMAIL);
		if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
			$responseArray['email']['responseState'] = 0;
			$responseArray['email']['responseMessage'] = "Please enter a valid email address.";
		} else if ($this->checkUserEmailExistence($email) == 1) {
			$responseArray['email']['responseState'] = 0;
			$responseArray['email']['responseMessage'] = "An account with that email address already exists.";
		} else {
			$responseArray['email']['responseState'] = 1;
			$responseArray['email']['responseMessage'] = '';
		}

		// Validate password
		if (strlen($password) < 8) {
			$responseArray['password']['responseState'] = 0;
			$responseArray['password']['responseMessage'] = "Please choose a password with at least 8 characters.";
		} else if ($password != $confirmPassword) {
			$responseArray['password']['responseState'] = 0;
			$responseArray['password']['responseMessage'] = "Passwords entered don't match. Please try again.";
		} else {
			$responseArray['password']['responseState'] = 1;
			$responseArray['password']['responseMessage'] = '';
		}

		// Validate First Name
		if (strlen($firstName) < 1) {
			$responseArray['firstName']['responseState'] = 0;
			$responseArray['firstName']['responseMessage'] = "Please add a first name..";
		} else {
			$responseArray['firstName']['responseState'] = 1;
			$responseArray['firstName']['responseMessage'] = '';
		}

		// Validate last name
		if (strlen($lastName) < 2) {
			$responseArray['lastName']['responseState'] = 0;
			$responseArray['lastName']['responseMessage'] = "Please choose a Last Name with at least 2 characters.";
		} else {
			$responseArray['lastName']['responseState'] = 1;
			$responseArray['lastName']['responseMessage'] = '';
		}

		// Validate nickname
		if (strlen($nickname) < 4) {
			$responseArray['nickname']['responseState'] = 0;
			$responseArray['nickname']['responseMessage'] = "Please enter a nickname of at least 4 characters.";
		} else if ($this->checkNicknameExistence($nickname) == 1) {
			$responseArray['nickname']['responseState'] = 0;
			$responseArray['nickname']['responseMessage'] = "Sorry, that nickname is already taken. Please choose another.";
		} else {
			$responseArray['nickname']['responseState'] = 1;
			$responseArray['nickname']['responseMessage'] = '';
		}

		// If input is valid, create the user.
		$inputIsValid = 1;
		foreach($responseArray as $response) {
			if ($response['responseState'] == 0) {
				$inputIsValid = 0;
				break;
			}
		}


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
				 :email
				,:nickname
				,:firstName
				,:lastName
				,:password
				,:salt
				,:isAdmin
			)
SQL;

			$stmt = $this->conn->prepare($sql);
            $params = array(
                 'email' => $email
                ,'nickname' => $nickname
                ,'firstName' => $firstName
                ,'lastName' => $lastName
                ,'password' => $password
                ,'salt' => $salt
                ,'isAdmin' => intval($isAdmin)
            );
			$stmt->execute($params);

		}

		return $responseArray;

	}

	function checkUserEmailExistence($email) {

		$emailExists = 0;

		$stmt = $this->conn->prepare("SELECT user_id FROM toonces.users WHERE LOWER(email) = LOWER(?)");
		$stmt->execute(array($email));

		foreach ($stmt as $row) {
			$emailExists = 1;
		}

		return $emailExists;

	}

	function checkNicknameExistence($paramNickname) {

		$nicknameExists = 0;

		$stmt = $this->conn->prepare("SELECT user_id FROM toonces.users WHERE LOWER(nickname) = LOWER(?)");
		$stmt->execute(array($paramNickname));

		foreach ($stmt as $row) {
			$nicknameExists = 1;
		}

		return $nicknameExists;

	}

}