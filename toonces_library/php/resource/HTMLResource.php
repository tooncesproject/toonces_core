<?php

require_once LIBPATH.'php/toonces.php';

class HTMLResource implements iResource {

	var $html;
	var $htmlHeader;
	var $htmlFooter;
	var $pageViewReference;



	protected function getResource() {

		//add header and footer
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;

		return $this->html;
	}
}
