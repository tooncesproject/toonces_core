<?php

require_once ROOTPATH.'/toonces.php';

class SessionManager
{
	
	var $conn;
	var $userId;
	var $nickname;
	var $userIsAdmin;
	var $userFirstName;
	var $userLastName;

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
	function login($email,$formPassword) {
		
		//vars
		$userId = 0;
		$nickname = '';
		
		$dbPassword = '';
		
		$this->conn = UniversalConnect::doConnect();
		
		
		
		$sql = <<<SQL
        
        SELECT
             user_id
            ,password
            ,nickname
			,firstname
			,lastname
			,salt
			,is_admin
        FROM
            toonces.users
        WHERE
            email = '%s';
SQL;
		
		$query = sprintf($sql,$email);
		
		// query and check for match
		$result = $this->conn->query($query);
		
		foreach ($result as $row) {
			$dbPassword = $row['password'];
			$this->userId = $row['user_id'];
			$this->nickname = $row['nickname'];
			$this->userFirstName = $row['firstname'];
			$this->userLastName = $row['lastname'];
			$this->salt = $row['salt'];
			$this->userIsAdmin = $row['is_admin'];
		}
		
		// if successful, begin session

		$formPassword = hash('sha512', $formPassword.$this->salt);
		if ($formPassword != '' and $dbPassword == $formPassword) {
			$loginSuccess = 1;
			// set sesh vars
			$_SESSION['userId'] = $this->userId;
			$_SESSION['nickname'] = $this->nickname;
			$_SESSION['userFirstName'] = $this->userFirstName;
			$_SESSION['userLastName'] = $this->userLastName;
			$_SESSION['userIsAdmin'] = $this->userIsAdmin;
			
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
	
	function checkBruteForce() {
		
	}
	
}