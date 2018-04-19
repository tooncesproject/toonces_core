<?php

require_once LIBPATH.'php/toonces.php';

class SessionManager
{

	var $conn;
	var $userId;
	var $nickname;
	var $userIsAdmin;
	var $userFirstName;
	var $userLastName;
	var $adminSessionActive;
	private $loginString;

	function __construct($paramSQLConn) {
	    $this->conn = $paramSQLConn;
	}
	
// Session functions

	// Check session
	function checkSession() {

		$this->beginSession();
		$this->adminSessionActive = 0;
		$dbPassword = '';
		$dbUserIsAdmin = 0;
		$userAgentString = $_SERVER['HTTP_USER_AGENT'];
		// Are session variables set?
		if (
			isset($_SESSION['userId'])
			and 
			isset($_SESSION['userFirstName'])
			and
			isset($_SESSION['userLastName'])
			and
			isset($_SESSION['loginString'])
		) {
			$seshUserId = $_SESSION['userId'];
			$seshLoginString = $_SESSION['loginString'];

			// Query database for user's hashed password
			$SQL = <<<SQL
			SELECT
				 password
				,is_admin
			FROM 
				toonces.users 
			WHERE 
				user_id = :userid;
SQL;

			$stmt = $this->conn->prepare($SQL);
			$stmt->execute(array(':userid' => $seshUserId));
			foreach ($stmt as $row) {
				$dbPassword = $row['password'];
				$dbUserIsAdmin = $row['is_admin'];
				$this->nickname = $_SESSION['nickname'];
				$this->userFirstName = $_SESSION['userFirstName'];
				$this->userLastName = $_SESSION['userLastName'];
			}

			//Check for matching login string.
			$activeLoginString = hash('sha512',$dbPassword,$userAgentString);
			if ($activeLoginString == $seshLoginString) {
				$this->adminSessionActive = 1;
				$this->userIsAdmin = $dbUserIsAdmin;
				$this->userId = $seshUserId;
			}
		}
	}

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

		// set session name
		session_name($sessionName);

		//start session
		session_start();

		// regenerate session
		session_regenerate_id(true);
	}

	// login
	function login($email,$formPassword, $pageId) {

		//vars
		$userId = 0;
		$nickname = '';
		$dbPassword = '';

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

		// Record login attempt
		$loginAttemptId = null;
		$sql = <<<SQL
        CALL sp_record_login_attempt
        (   
             :pageId    		              -- param_attempt_page_id     BIGINT
            ,:httpMethod		              -- param_http_method              VARCHAR(10)
            ,:userId 		                 -- param_attempt_user_id          BIGINT UNSIGNED
            ,:attemptTime		             -- param_attempt_time             TIMESTAMP
            ,INET_ATON(:httpClientIp)		    -- param_http_client_ip           INT UNSIGNED
            ,INET_ATON(:httpXForwardedFor)		-- param_http_x_forwarded_for     INT UNSIGNED
            ,INET_ATON(:httpRemoteAddr)		      -- param_remote_addr              INT UNSIGNED
            ,:userAgent		                   -- param_user_agent               VARCHAR(1000)
        )
SQL;
		$stmt = $this->conn->prepare($sql);
		$loginAttemptVars = array(
		     'pageId' => $pageId
		    ,'userId' => isset($this->userId) ? strval($this->userId) : null
		    ,'attemptTime' => date('Y-m-d h:i:s', time())
		    ,'httpClientIp' => isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null
		    ,'httpXForwardedFor' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null
		    ,'httpRemoteAddr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null
		    ,'userAgent' => $_SERVER['HTTP_USER_AGENT']
		);
        $stmt->execute($loginAttemptVars);
        $result = $stmt->fetchAll();
        $loginAttemptId = $result[0][0];
        
		// Check for brute-force attack
		$bruteForce = $this->checkBruteForce();

		// if successful, begin session
		$this->salt = isset($this->salt) ? $this->salt : '';
		$formPassword = hash('sha512', $formPassword.$this->salt);
		if ($formPassword != '' and $dbPassword == $formPassword and $bruteForce == 0) {
			$loginSuccess = 1;
			// Set login state
			$this->loginString = hash('sha512',$dbPassword, $_SERVER['HTTP_USER_AGENT']);
			// Update the record in login_attempt indicating success
			$sql = "UPDATE login_attempt SET attempt_success = TRUE WHERE login_attempt_id = :loginAttemptId";
			$stmt = $this->conn->prepare($sql);
			$stmt->execute(array('loginAttemptId' => $loginAttemptId));
		} else {
			$loginSuccess = 0;
		}

		return $loginSuccess;
	}

	function setSessionParams() {
	       // Check that the necessary instance variables are set
	       $sessionValid = isset($this->userId) && isset($this->nickname) && isset($this->userFirstName) && isset($this->userLastName);
	       if (!$sessionValid) 
	           throw new Exception('Programming error: setSessionParams must only be called after login function.');
	       
	       // Otherwise: Set sesh vars.
           $_SESSION['userId'] = $this->userId;
	       $_SESSION['nickname'] = $this->nickname;
	       $_SESSION['userFirstName'] = $this->userFirstName;
	       $_SESSION['userLastName'] = $this->userLastName;
	       $_SESSION['loginString'] = $this->loginString;
	       $this->adminSessionActive = 1;
	}

	function logout() {

		//run session start function
		$this->beginSession();

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

		// Check for prior login attempts
		$tenMinutesAgo = date('Y-m-d h:i:s', time() - (10 * 60));
		if (isset($this->userId)) {
			$checkUserID = $this->userId;
		} else {
			$checkUserID = 0;
		}

		$SQL = <<<SQL
		SELECT
			COUNT(*) AS attemptcount
		FROM
			toonces.login_attempts
		WHERE
			attempt_time >= '%s'
        AND
            attempt_success = FALSE
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