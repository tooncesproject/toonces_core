<?php
class DataEntry
{
	//Variable for MySql connection
	private $hookup;
	private $sql;
	private $tableMaster;
	
	//Field Variables
	private $name;
	private $email;
	private $lang;
	
	public function __construct()
	{
	    //Get table name and make connection
            $this->tableMaster="basics";
	    $this->hookup=UniversalConnect::doConnect();
	    
	    //Get data from HTML form
	    $this->name=$_POST['name'];
	    $this->email=$_POST['email'];
	    $this->lang=$_POST['lang'];
	    
	    //Call private methods for MySql operations
	    $this->doInsert();
	    $this->hookup->close();
	}

	private function doInsert()
	{
		$this->sql = "INSERT INTO $this->tableMaster (name,email,lang) VALUES ('$this->name','$this->email', '$this->lang')";

		try
		{	
			$this->hookup->query($this->sql);
			printf("User name: %s <br/> Email: %s <br/> uses %s the most for programming.",$this->name,$this->email,$this->lang);
		}
	
		catch (Exception $e)
		{
			echo "There is a problem: " . $e->getMessage();
			exit();
		}
	}
}
?>