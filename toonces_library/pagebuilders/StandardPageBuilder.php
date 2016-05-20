<?php

require_once ROOTPATH.'/toonces.php';

class StandardPageBuilder extends PageBuilder {

	var $contentElement;

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

		// Acquire the toonces-configuration.xml file
		$xmlReader = new XMLReader();
		try {
			$xmlReader->open('ROOTPATH.toonces-configuration.xml');
		} catch (Exception $e) {
			die('Error reading the config file (toonces-configuration.xml). Consult documentation for help.');
		}

		// does this work?
		while ($xmlReader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'standard_page') {
				$headerHTMLFile = $reader->getAttribute('header_html_file');
				// no body tag? because it's fucking dumb.
				//$bodyTagHTMLFile = $reader->getAttribute('body_tag_html_file');
				$footerHTMLFile = $reader->getAttribute('footer_html_file');
			}
		}
		
		$this->buildElementArray();

		return $this->elementArray;

	}

	function buildElementArray() {
		// get static/generic html header, create as element
		$htmlHeaderElement = new Element($this->pageViewReference);
		$htmlHeaderElement->setHTML(file_get_contents(ROOTPATH.'/static_data/generic_html_header.html'));

		array_push($this->elementArray, $htmlHeaderElement);

		$headElement = new HeadElement($this->pageViewReference);

		// get head attributes
		$headElement->setPageTitle($this->pageViewReference->getPageTitle());
		$headElement->setStyleSheet($this->pageViewReference->getStyleSheet());

		$headElement->setHeadTags(file_get_contents(ROOTPATH.'/static_data/head_tags.html'));

		array_push($this->elementArray, $headElement);

		$bodyStartElement = new Element($this->pageViewReference);

		$bodyStartElement->setHTML(file_get_contents(ROOTPATH.'/static_data/body_start.html'));
		array_push($this->elementArray, $bodyStartElement);

		// If there's a toolbar, add it here.
		if (isset($this->toolbarElement)) {
			array_push($this->elementArray, $this->toolbarElement);
		}

		// After the toolbar,add the header element
		$pageHeader = new Element($this->pageViewReference);

		//$pageHeader->setHTML(file_get_contents(ROOTPATH.'/static_data/body_test.html'));
		array_push($this->elementArray, $pageHeader);

		$pageId = $this->pageViewReference->pageId;

		// Add the content element, which holds the page content.
		array_push($this->elementArray, $this->contentElement);

		$footerElement = new Element($this->pageViewReference);

		// to do: read footer path from xml
		//$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));

		array_push($this->elementArray, $footerElement);

	}
}
