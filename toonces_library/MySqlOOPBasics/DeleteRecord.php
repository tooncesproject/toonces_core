<?php
class DeleteRecord
{
        //Variables for MySql connection
	private $hookup;
	private $sql;
	private $tableMaster;
        
        //From HTML
        private $deadman;
	
	public function __construct()
	{
	    $this->deadman =intval($_POST['idd']);
            //Get table name and make connection
            $this->tableMaster="basics";
	    $this->hookup=UniversalConnect::doConnect();
	    $this->recordKill();
	    $this->hookup->close();	
	}
	
	private function recordKill()
	{
            //Create Query Statement
	    $this->sql ="Delete FROM $this->tableMaster WHERE id='$this->deadman'";
	
	    try
	    {
		$result = $this->hookup->query($this->sql);
		printf("Record with ID=%s: has been dropped.<br />",$this->deadman );
	    }
	    catch(Exception $e)
	    {
		echo "Here's what went wrong: " . $e->getMessage();
	    }
	}
}
?>