<?php
interface iView
{
	
	public function setHtmlHeader ($headerString);
	public function setHtmlFooter ($footerString);
	
	public function getHTML();
}

?>