<?php

class SQLConn {
	
	var $conn;
	
	public static function getSQLConn() {
		
		if (object_empty($conn)) {
			// establish PDO mySQL connection
			$servername = 'localhost';
			$username = 'root';
			
			try {
				$conn = new PDO('mysql:host=$servername;dbname=toonces',$username);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch (PDOException $e)
			{
				echo"Connection failed: ". $e->getMessage();
			}
		}
	}
	
	function object_empty($obj) {
		foreach ($obj as $x) return false;
		return true;
	}
	
}