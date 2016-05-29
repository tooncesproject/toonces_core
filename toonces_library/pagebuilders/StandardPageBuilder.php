<?php

require_once LIBPATH.'toonces.php';

class StandardPageBuilder extends PageBuilder {

	var $contentElement;
	var $headerHTML;
	var $footerHTML;
	var $bodyViewElement;

	function buildPage() {

		// Check for edit mode signal from GET, and if applicable, check for user access.
		$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';

		// If user doesn't have editing capability, ignore the mode.
		if ($this->pageViewReference->userCanEdit == false) {
			$mode = '';
		}
		switch ($mode) {
			default:
				$this->contentElement = new Element($this->pageViewReference);
		}

		// Instantiate the BodyViewElement
		$this->bodyViewElement = new BodyViewElement($this->pageViewReference);

		// Acquire the toonces-configuration.xml file
		$xmlReader = new XMLReader();
		$xmlReader->open(ROOTPATH.'toonces-config.xml');

		// use this hideous fucking code to dig into the XML
		while ($xmlReader->read()) {
			if ($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->name == 'standard_page') {
				$headerHTMLFile = $xmlReader->getAttribute('header_html_file');
				$this->headerHTML = file_get_contents(ROOTPATH.$headerHTMLFile);

				$footerHTMLFile = $xmlReader->getAttribute('footer_html_file');
				$this->footerHTML = file_get_contents(ROOTPATH.$footerHTMLFile);

				$pageNode = $xmlReader->expand();
				$subNodes = $pageNode->childNodes;
				for ($i = 0; $i < $subNodes->length; $i++) {
					$child = $subNodes->item($i);
					if ($child->nodeName == 'body_attributes') {
						$attributes = $child->childNodes;
						for ($n = 0; $n < $attributes->length; $n++) {
							$attributeNode = $attributes->item($n);
							if ($attributeNode->nodeName == 'html_attribute' && $attributeNode->hasAttributes()) {
								$nodeAttributes = $attributeNode->attributes;
								$keyItem = $nodeAttributes->getNamedItem('key');
								$valueItem = $nodeAttributes->getNamedItem('value');
								//echo $keyItem->nodeValue;
								$this->bodyViewElement->addBodyAttribute($keyItem->nodeValue, $valueItem->nodeValue);
							}
						}
					}
				}
			}
		}

		$this->buildElementArray();

		return $this->elementArray;

	}

	function buildElementArray() {
		// get static/generic html header, create as element
		$htmlHeaderElement = new Element($this->pageViewReference);
		$htmlHeaderElement->setHTML(file_get_contents(LIBPATH.'/static_data/generic_html_header.html'));
		array_push($this->elementArray, $htmlHeaderElement);
		$headElement = new HeadElement($this->pageViewReference);

		// get head attributes
		$headElement->setPageTitle($this->pageViewReference->getPageTitle());
		$headElement->setStyleSheet($this->pageViewReference->getStyleSheet());
		$headElement->setHeadTags(file_get_contents(LIBPATH.'/static_data/head_tags.html'));
		array_push($this->elementArray, $headElement);

		// Add everything below to the Body Element

		// If there's a toolbar, add it here.
		if (isset($this->toolbarElement)) {
			$this->bodyViewElement->addElement($this->toolbarElement);
		}

		// After the toolbar, add the header element
		$pageHeader = new Element($this->pageViewReference);
		$pageHeader->setHTML($this->headerHTML);
		$this->bodyViewElement->addElement($pageHeader);

		$pageId = $this->pageViewReference->pageId;

		// Add the content element, which holds the page content.
		array_push($this->elementArray, $this->contentElement);
		$this->bodyViewElement->addElement($this->contentElement);

		// Add the content footer element
		$footerElement = new Element($this->pageViewReference);
		$footerElement->setHTML($this->footerHTML);
		$this->bodyViewElement->addElement($footerElement);

		// Add the bodyViewElement to the page element array
		array_push($this->elementArray, $this->bodyViewElement);

		//Finally, create an element object with the closing HTML tag.
		$closingElement = new Element($this->pageViewReference);
		$closingElement->setHTML('</html>'.PHP_EOL);
		array_push($this->elementArray, $closingElement);


	}
}
