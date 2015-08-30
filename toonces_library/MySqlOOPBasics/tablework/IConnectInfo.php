<?php
//Filename: IConnectInfo.php
interface IConnectInfo
{
	const HOST ="userHost";
	const UNAME ="userName";
	const PW ="passWord";
	const DBNAME = "dataBaseName";
	
	public static function doConnect();
}
?>