<?php
include_once ROOTPATH.'/interfaces/iView.php';
//include_once ROOTPATH.'/interfaces/iElement.php';
include_once ROOTPATH.'/test/fungussss.php';
include_once ROOTPATH.'/test/herro.php';
include_once ROOTPATH.'/Element.php';

class ViewElement extends Element implements iView
{
	// instance variables
	// inherited class variables commented out
	//var $htmlHeader;
	//var $htmlFooter;
	var $pageElements = array();
	var $elementsCount = 0;
	
	
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