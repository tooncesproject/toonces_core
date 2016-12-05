<?php

interface iConnectInfo {
	
	const HOST = 'localhost';
	const UNAME = 'toonces';
	const PW = 'kittycat';
	const DBNAME = 'toonces';
	
	public static function doConnect();
	
}

?>