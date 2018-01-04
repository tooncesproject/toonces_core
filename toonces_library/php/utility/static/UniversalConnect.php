<?php

include_once LIBPATH.'php/toonces.php';

class UniversalConnect
{

	private static $conn;

	public static function doConnect() {
		
		// get sql password from toonces-config.xml
		$xml = new DOMDocument();
		$xml->load(ROOTPATH.'toonces-config.xml');
		
		$hostNode = $xml->getElementsByTagName('sql_host')->item(0);
		$host = $hostNode->nodeValue;
		
		$portNode = $xml->getElementsByTagName('sql_port')->item(0);
        $port = $portNode->nodeValue;
        
        $dbNameNode = $xml->getElementsByTagName('sql_db_name')->item(0);
        $dbName = $dbNameNode->nodeValue;
		
        $dbUserNameNode = $xml->getElementsByTagName('sql_user_name')->item(0);
        $dbUserName = $dbUserNameNode->nodeValue;
        
        $passwordNode = $xml->getElementsByTagName('sql_password')->item(0);
		$pw = $passwordNode->nodeValue;

		if (isset(self::$conn) == false) {
			try {

				self::$conn = new PDO("mysql:host=$host;port=$port;dbname=$dbName",$dbUserName,$pw);
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