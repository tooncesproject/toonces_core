<?php

include_once LIBPATH.'php/toonces.php';

class UniversalConnect implements iConnectInfo
{

	private static $server = iConnectInfo::HOST;
	private static $currentDB = iConnectInfo::DBNAME;
	private static $user = iConnectInfo::UNAME;
	private static $conn;


	public static function doConnect() {

		$srv = self::$server;
		$db = self::$currentDB;
		$usr = self::$user;
		//$pw = self::$pass;
		
		// get sql password from toonces-config.xml
		$xml = new DOMDocument();
		$xml->load(ROOTPATH.'toonces-config.xml');
		$nodes = $xml->getElementsByTagName('sql_password');
		$passwordNode = $nodes->item(0);
		$pw = $passwordNode->nodeValue;

		if (isset(self::$conn) == false) {
			try {

				self::$conn = new PDO("mysql:host=$srv;dbname=$db",$usr,$pw);
				self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e)
			{
				echo"Connection failed: ". $e->getMessage();
			}
		}

		return self::$conn;

	}
}

?>