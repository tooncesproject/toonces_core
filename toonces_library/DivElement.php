<?php
/*    woo
 * 
 * 
 * 
 * 
 */


include_once ROOTPATH.'/interfaces/iElement.php';
include_once ROOTPATH.'/Element.php';

class DivElement extends Element implements iView {

	// inherited class variables commented out
	//var $html;
	//var $htmlHeader;
	//var $htmlFooter;
	public $cssClass;

	public function __construct($initialCssClass) {
		
		$this->cssClass = $initialCssClass;
		
		if (empty($this->cssClass) == false ) {
			$this->htmlHeader = '<div class="'.$this->cssClass.'">'.PHP_EOL;
		} else {
			$this->htmlHeader = "<div>".PHP_EOL;
		}
		
		$this->htmlFooter = "</div>".PHP_EOL;
		
	}
	
	
	/*
	public function setHtmlHeader($headerString){
		$htmlHeader = $headerString;
	}
	
	public function setHtmlFooter($footerString) {
		$htmlFooter = $footerString;
	}
	*/
	
	
	public function setHTML($htmlString) {
		$this->html = $htmlString;
	}
	
	public function getHTML() {
		
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;
		
		return $this->html;
	}
}
