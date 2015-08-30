<?php

include_once ROOTPATH.'/interfaces/iElement.php';

class Element implements iView {

	var $html;
	var $htmlHeader;
	var $htmlFooter;
	
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
