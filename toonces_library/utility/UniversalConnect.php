<?php

INCLUDE_ONCE ROOTPATH.'/interfaces/iConnectInfo.php';

class UniversalConnect implements iConnectInfo
{
	
	private static $server = iConnectInfo::HOST;
	private static $currentDB = iConnectInfo::DBNAME;
	private static $user = iConnectInfo::UNAME;
	private static $pass = iConnectInfo::PW;
	private static $conn;
	
	
	public static function doConnect() {
		
		$srv = self::$server;
		$db = self::$currentDB;
		$usr = self::$user;
		$pw = self::$pass;
		
		try {

			$conn = new PDO("mysql:host=$srv;dbname=$db",$usr,$pw);
			//$conn = new PDO("mysql:host=localhost;dbname=toonces",'toonces','kittycat');
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//echo 'CONNECTION SUCCESSFUL!!';
		}
		catch (PDOException $e)
		{
			echo"Connection failed: ". $e->getMessage();
		}
		
		//return self::$conn;
		return $conn;
	
	}
}

?>