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
		
		// Check for brute-force attack and record login attempt
		$bruteForce = $this->checkBruteForce();
		
		// if successful, begin session
		$this->salt = isset($this->salt) ? $this->salt : '';
		$formPassword = hash('sha512', $formPassword.$this->salt);
		if ($formPassword != '' and $dbPassword == $formPassword and $bruteForce == 0) {
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
		
		// build query array
		$loginAttemptVars = array();
		
		$loginAttemptVars[':attempt_user_id'] = isset($this->userId) ? strval($this->userId) : null;
		$loginAttemptVars[':attempt_time'] = date('Y-m-d h:i:s', time());
		$loginAttemptVars[':http_client_ip'] = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null;
		$loginAttemptVars[':http_x_forwarded_for'] = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
		$loginAttemptVars[':remote_addr'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		$loginAttemptVars[':user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
		// Insert login attempt record
		$stmt = <<<SQL
		INSERT INTO toonces.login_attempts
		(
			 attempt_user_id
			,attempt_time
			,http_client_ip
			,http_x_forwarded_for
			,remote_addr
			,user_agent
		) VALUES (
			 :attempt_user_id
			,:attempt_time
			,INET_ATON(:http_client_ip)
			,INET_ATON(:http_x_forwarded_for)
			,INET_ATON(:remote_addr)
			,:user_agent
		)	
SQL;

		$stmt = $this->conn->prepare($stmt,$loginAttemptVars);
		$stmt->execute($loginAttemptVars);

		// Check for prior login attempts
		$tenMinutesAgo = date('Y-m-d h:i:s', time() - (10 * 60));
		if (isset($this->userId)) {
			$checkUserID = $this->userId;
		} else {
			$checkUserID = 0;
		}
		/*
		$checkAttemptVars = array();
		
		$checkAttemptVars[':ten_mintues_ago'] = date('Y-m-d h:i:s',$tenMinutesAgo);
		$checkAttemptVars[':http_client_ip'] = $loginAttemptVars[':http_client_ip'];
		$checkAttemptVars[':http_x_forwarded_for'] = $loginAttemptVars[':http_x_forwarded_for'];
		$checkAttemptVars[':remote_addr'] = $loginAttemptVars [':remote_addr'];
		$checkAttemptVars[':user_id'] = $loginAttemptVars[':attempt_user_id'];
	*/
		$SQL = <<<SQL
		SELECT
			COUNT(*) AS attemptcount
		FROM
			toonces.login_attempts
		WHERE
			attempt_time >= '%s'
		AND (
			http_client_ip = INET_ATON('%s')
		OR
			http_x_forwarded_for = INET_ATON('%s')
		OR
			remote_addr = INET_ATON('%s')
		OR
			attempt_user_id = %s
		);
SQL;

		$SQL = sprintf
		(
			 $SQL
			,$tenMinutesAgo
			,$loginAttemptVars[':http_client_ip']
			,$loginAttemptVars[':http_x_forwarded_for']
			,$loginAttemptVars[':remote_addr']
			,$checkUserID
		);

		$checkAttemptResponse = $this->conn->query($SQL);

		foreach ($checkAttemptResponse as $row)
			$attemptCount = $row['attemptcount'];
	
		// If more than 30 attempts in the past 10 minutes, reject login attempt.
		if ($attemptCount > 30) {
			return 1;
		} else {
			return 0;
		}

	}
	
}