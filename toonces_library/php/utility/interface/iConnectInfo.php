<?php

interface iConnectInfo {
	
	const HOST = '127.0.0.1';
	const UNAME = 'toonces';
	const PW = 'kittycat';
	const DBNAME = 'toonces';
	
	public static function doConnect();
	
}

?>