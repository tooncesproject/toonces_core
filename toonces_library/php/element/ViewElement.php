<?php

include_once LIBPATH.'php/toonces.php';

class ViewElement extends Element implements iElement
{

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
