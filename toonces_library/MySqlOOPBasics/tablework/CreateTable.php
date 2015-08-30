<?php
ini_set("display_errors","1");
ERROR_REPORTING(E_ALL);
include_once('../../MySqlOOPBasics/tablework/UniversalConnect.php');
class CreateTable
{
	private $tableMaster;
	private $hookup;
	private $sql;
	
	public function __construct()
	{
		$this->tableMaster="basics";
		$this->hookup=UniversalConnect::doConnect();
	
		$drop = "DROP TABLE IF EXISTS $this->tableMaster";
	
		if($this->hookup->query($drop) === true)
		{
			printf("Old table %s has been dropped.<br/>",$this->tableMaster);
		}

		$this->sql = "CREATE TABLE $this->tableMaster (
			id SERIAL,
			name NVARCHAR(30),
			email NVARCHAR(36),
			lang NVARCHAR(10),
			PRIMARY KEY(id)
			)";
	
		if($this->hookup->query($this->sql) === true)
		{
			printf("Table $this->tableMaster has been created successfully.<br/>");
		}
		$this->hookup->close();
	}
}

$worker=new CreateTable();
?>
