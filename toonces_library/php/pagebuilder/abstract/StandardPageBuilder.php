<?php
/*
 * StandardPageBuilder
 * Initial commit: Paul Anderson, 5/28/2016
 * 
 * An abstract PageBuilder class that automagically generates a page based on
 * input from the toonces-config.xml file.
 * Extend it by overriding the createContentElement method to insert
 * custom content in the page.
 * 
 */


require_once LIBPATH.'php/toonces.php';

abstract class StandardPageBuilder extends PageBuilder {

	var $contentElement;
	var $headerHTML;
	var $footerHTML;
	var $headTagsHTML;
	var $bodyHTMLViewResource;
	var $cssStyleSheet;

	function createContentElement() {

		// Insert code here to create a content element
		// $this->contentElement = new HTMLResource($this->pageViewReference);

	}

	function buildPage() {

		// Instantiate the BodyHTMLViewResource
		$this->bodyHTMLViewResource = new BodyHTMLViewResource($this->pageViewReference);

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
				
				$headTagsHTMLFile = $xmlReader->getAttribute('head_tags_file');
				$this->headTagsHTML = file_get_contents(ROOTPATH.$headTagsHTMLFile);
				
				$this->cssStyleSheet = $xmlReader->getAttribute('css_stylesheet_file');
				

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
								$this->bodyHTMLViewResource->addBodyAttribute($keyItem->nodeValue, $valueItem->nodeValue);
							}
						}
					}
				}
			}
		}

		// Call the createContentElement method to acquire the content element

		$this->createContentElement();

		$this->buildElementArray();

		return $this->elementArray;

	}

	function buildElementArray() {
		// get static/generic html header, create as element
		$htmlHeaderElement = new HTMLResource($this->pageViewReference);
		$htmlHeaderElement->html = file_get_contents(LIBPATH.'/html/generic_html_header.html');
		array_push($this->elementArray, $htmlHeaderElement);
		$headElement = new HeadElement($this->pageViewReference);

		// get head attributes
		$headElement->pageTitle = $this->pageViewReference->getPageTitle();
		$headElement->styleSheet = $this->cssStyleSheet;
		$headElement->headTags = $this->headTagsHTML;
		array_push($this->elementArray, $headElement);

		// Add everything below to the Body Element

		// If there's a toolbar, add it here.
		if (isset($this->toolbarElement))
			$this->bodyHTMLViewResource->addElement($this->toolbarElement);

		// After the toolbar, add the header element
		$pageHeader = new HTMLResource($this->pageViewReference);
		$pageHeader->html = $this->headerHTML;
		$this->bodyHTMLViewResource->addElement($pageHeader);

		$pageId = $this->pageViewReference->pageId;

		// Add the content element, which holds the page content.
		$this->bodyHTMLViewResource->addElement($this->contentElement);

		// Add the content footer element
		$footerElement = new HTMLResource($this->pageViewReference);
		$footerElement->html = $this->footerHTML;
		$this->bodyHTMLViewResource->addElement($footerElement);

		// Add the bodyHTMLViewResource to the page element array
		array_push($this->elementArray, $this->bodyHTMLViewResource);

		//Finally, create an element object with the closing HTML tag.
		$closingElement = new HTMLResource($this->pageViewReference);
		$closingElement->html = '</html>'.PHP_EOL;
		array_push($this->elementArray, $closingElement);


	}
}
