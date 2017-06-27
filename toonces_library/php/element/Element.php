<?php

include_once LIBPATH.'php/toonces.php';

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

		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;

		return $this->html;
	}
}
