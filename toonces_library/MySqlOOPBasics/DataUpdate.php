<?php
class DataUpdate
{
   private $hookup;
   private $tableMaster;
   private $sql;
   //Fields
   private $id;
   private $name;
   private $email;
   private $lang;
   
   public function __construct()
   {
      $this->id=intval($_POST['id']);
      $this->name=$_POST['name'];
      $this->email=$_POST['email'];
      $this->lang=$_POST['lang'];
        
      $this->tableMaster="basics";
      $this->hookup=UniversalConnect::doConnect();
      
       //Call each update
      $this->doName();
      $this->doEmail();
      $this->doLang();
	
	//Close once
      $this->hookup->close();
   }
   
   private function doName()
   {
      $this->sql ="UPDATE $this->tableMaster SET name='$this->name' WHERE id='$this->id'";
      try
      {
	 $result = $this->hookup->query($this->sql);
	 echo "Name update complete.<br />";
      }
      catch(Exception $e)
      {
	 echo "Here's what went wrong: " . $e->getMessage();
      } 
   }

   private function doEmail()
   {
      $this->sql ="UPDATE $this->tableMaster SET email='$this->email' WHERE id='$this->id'";
      try
      {
	 $result = $this->hookup->query($this->sql);
	 echo "Name update complete.<br />";
      }
      catch(Exception $e)
      {
	 echo "Here's what went wrong: " . $e->getMessage();
      } 
   }
   
   private function doLang()
   {
      $this->sql ="UPDATE $this->tableMaster SET lang='$this->lang' WHERE id='$this->id'";
      try
      {
	 $result = $this->hookup->query($this->sql);
      	 echo "Computer language update complete.<br />";
      }
      catch(Exception $e)
      {
	 echo "Here's what went wrong: " . $e->getMessage();
      } 
   }
}
?>