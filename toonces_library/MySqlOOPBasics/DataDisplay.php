<?php
class DataDisplay
{
	//Variable for MySql connection
	private $hookup;
	private $sql;
	private $tableMaster;
	
	public function __construct()
	{
	    //Get table name and make connection
            $this->tableMaster="basics";
	    $this->hookup=UniversalConnect::doConnect();
	    $this->doDisplay();
	    $this->hookup->close();	
	}
	
	private function doDisplay()
	{
            //Create Query Statement
	    $this->sql ="SELECT * FROM $this->tableMaster";
	
	    try
	    {
		$result = $this->hookup->query($this->sql);

		while ($row = $result->fetch_assoc()) 
		{
		    printf("ID: %s Name: %s Email: %s <br />Computer Language: %s<p />",$row['id'], $row['name'],$row['email'],$row['lang'] );
		}
                
		$result->close();
	    }
	    catch(Exception $e)
	    {
		echo "Here's what went wrong: " . $e->getMessage();
	    }
	}
}
?>