<?php
/*
 * BodyViewElement
 * Initial commit: Paul Anderson, 5/28/2016
 * 
 *  Provides the body element of an HTML page. As an extension of
 *  ViewElement, it allows other Element objects to be nested inside it.
 *  It overrides the getHTML function with a call to add body tags as 
 *  the HTML headers and footers and adds a function to add (n) HTML attribute
 *  key-value pairs to the body HTML element.
 *  
 */


include_once LIBPATH.'php/toonces.php';

class BodyViewElement extends ViewElement implements iElement
{

	var $pageElements = array();
	var $elementsCount = 0;
	var $bodyAttributes = array();

	public function addBodyAttribute($key, $value) {
		// Create an array to store the key-value pair.
		$newBodyAttribute = array(
			 'key' => $key
			,'value' => $value
		);

		// Add the attribute array to the bodyAttributes array
		array_push($this->bodyAttributes, $newBodyAttribute);
	}

	private function buildBodyTag() {

		$bodyTagStart = '<body';

		// Iterate through the attribute array and add the attributes to the 
		// opening body HTML tag, if they are set.
		foreach ($this->bodyAttributes as $attribute) {
			// Build the attribute string.
			$bodyTagStart = $bodyTagStart.' '.$attribute['key'].'="'.$attribute['value'].'"';
		}

		$bodyTag = $bodyTagStart.'>';

		$this->htmlHeader = $bodyTag;
		$this->htmlFooter = '</body>';
	}

	// execution method

	public function getHTML() {

		$pageString = "";

		$this->buildBodyTag();

		foreach($this->pageElements as $object) {
			$pageString = $pageString.$object->getHTML().PHP_EOL;
		}

		$htmlString = $this->htmlHeader.PHP_EOL.$pageString.PHP_EOL.$this->htmlFooter.PHP_EOL;
		return $htmlString;

	}
}
