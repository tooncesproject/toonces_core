<?php

include_once LIBPATH.'php/toonces.php';

class ViewElement extends Element implements iResource
{

	var $pageElements = array();
	var $elementsCount = 0;

	public function addElement ($element) {

		array_push($this->pageElements,$element);
		$this->elementsCount++;

	}

	// execution method

	public function getResource() {

		$pageString = "";

		foreach($this->pageElements as $object) {
			$pageString = $pageString.$object->getResource().PHP_EOL;
		}

		$htmlString = $this->htmlHeader.$pageString.$this->htmlFooter;
		return $htmlString;

	}
}
