<?php

include_once LIBPATH.'php/toonces.php';

class Element implements iResource {

	var $html;
	var $htmlHeader;
	var $htmlFooter;
	var $pageViewReference;

	public function __construct($pageView) {
		$this->pageViewReference = $pageView;
	}

	public function getResource() {

		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;

		return $this->html;
	}
}
