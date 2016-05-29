<?php

include_once LIBPATH.'toonces.php';

class ViewElement extends Element implements iElement
{
	// instance variables
	// inherited class variables commented out
	//var $htmlHeader;
	//var $htmlFooter;
	var $pageElements = array();
	var $elementsCount = 0;
	
	//override element constructor method
//	public function __construct() {
		// do nothing
//	}
	
	
	// setter methods

	public function setHtmlHeader ($headerString) {
		$this->htmlHeader = $headerString;
	}
	
	public function setHtmlFooter ($footerString) {
		$this->htmlFooter = $footerString;
	}
	
	public function addElement ($element) {
		
		array_push($this->pageElements,$element);
		$this->elementsCount++;
		
	}
	
	// execution method
	
	public function getHTML() {
			
		$pageString = "";
		
		foreach($this->pageElements as $object) {
			$pageString = $pageString.$object->getHTML().PHP_EOL;	
		}
		
		$htmlString = $this->htmlHeader.$pageString.$this->htmlFooter;
		return $htmlString;
		
	}
	
	
		 	
}

?>