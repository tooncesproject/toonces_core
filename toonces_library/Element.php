<?php

include_once ROOTPATH.'/interfaces/iElement.php';

class Element implements iElement {

	var $html;
	var $htmlHeader;
	var $htmlFooter;
	var $pageViewReference;
	
	public function __construct($pageView) {
		$this->pageViewReference = $pageView;
	}
	
	
	public function setHtmlHeader($headerString){
		$htmlHeader = $headerString;
	}
	
	public function setHtmlFooter($footerString) {
		$htmlFooter = $footerString;
	}
	
	public function setHTML($htmlString) {
		$this->html = $htmlString;
	}
	
	public function getHTML() {
		return $this->html;
	}
}
