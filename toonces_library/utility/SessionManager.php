<?php

require_once ROOTPATH.'/toonces.php';

class SessionManager
{
	
	var $conn;
	var $userId;
	var $nickname;

// Session functions
// Start session

	function beginSession() {
		$sessionName = 'secure_session';
		$secure = 'SECURE';
		$httponly = true;
		
		// force session to use only cookies
		if (ini_set('session.use_only_cookies', 1) === FALSE) {
			header("Location:../error.php?err=Could Not initiate a safe session(ini_set)");
			exit();
		}

		// get current cookie parameters
		$cookieParams = session_get_cookie_params();
/*
		session_set_cookie_params(
			 $cookieParams['lifetime']
				,$cookieParams['path']
				,$cookieParams['domain']
				//,$secure
				,$httponly
		);
*/
		// set session name
		session_name($sessionName);

		//start session
		session_start();

		// regenerate session
		session_regenerate_id(true);
	}
	
	// login
	function login($email,$password) {
		
		//vars
		$userId = 0;
		$nickname = '';
		
		$userPassword = '';
		
		$this->conn = UniversalConnect::doConnect();
		
		
		
		$sql = <<<SQL
        
        SELECT
             user_id
            ,password
            ,nickname
        FROM
            toonces.users
        WHERE
            email = '%s';
SQL;
		
		$query = sprintf($sql,$email);
		
		// query and check for match
		$result = $this->conn->query($query);
		
		foreach ($result as $row) {
			$userPassword = $row['password'];
			$this->userId = $row['user_id'];
			$this->nickname = $row['nickname'];
		}
		
		// if successful, begin session
		if ($password != '' and $userPassword == $password) {
			$loginSuccess = 1;
			// set sesh vars
			$_SESSION['userId'] = $userId;
			$_SESSION['nickname'] = $nickname;
			
		} else {
			$loginSuccess = 0;
			
			
		}
		
		return $loginSuccess;
	}
	
	function logout() {
		
		//run session start function
		//$this->beginSession();
		
		// Unset session variables
		$_SESSION = array();
		
		// acquire session parameters
		$sessionParams = session_get_cookie_params();
		
		setcookie(
			session_name()
			,''
			, time() - 42000
			,$sessionParams["path"]
			,$sessionParams["domain"]
			,$sessionParams["secure"]
			,$sessionParams["httponly"]);
		
		session_destroy();
		
	}
	
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
		
		// $createUserStatus = 
		// validate email
		$email = filter_var($email,FILTER_VALIDATE_EMAIL);
		
		// If email valid, then check to see if it already exists.
		// If not, place email in error state 
		
	}
}