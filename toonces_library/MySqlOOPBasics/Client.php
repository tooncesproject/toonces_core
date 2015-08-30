<?php
ERROR_REPORTING( E_ALL | E_STRICT );
ini_set("display_errors", 1);
function __autoload($class_name) 
{
    include $class_name . '.php';
}
class Client
{
        //Variable to select the correct class
	private $task;
        
        //Which submit button used?
	public function __construct()
	{
	    if(isset($_POST['insert']))
            {
                unset($_POST['insert']);
                $this->task= new DataEntry();   
            }
            elseif(isset($_POST['all']))
            {
                unset($_POST['all']);
                $this->task= new DataDisplay();
            } 
            elseif(isset($_POST['update']))
            {
                unset($_POST['update']);
                $this->task= new DataUpdate();
            }
            elseif(isset($_POST['kill']))
            {
                unset($_POST['kill']);
                $this->task= new DeleteRecord();
            } 
	}	
}
$worker = new Client();
?>