<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';
include_once ROOTPATH.'/abstract/PageBuilder.php';
include_once ROOTPATH.'/BlogReader.php';
include_once ROOTPATH.'/DivElement.php';
include_once ROOTPATH.'/TagElement.php';
include_once ROOTPATH.'/HeadElement.php';
include_once ROOTPATH.'/PageView.php';

class FourOhFour extends PageBuilder {
	/*
	var $elementArray = array();
	private $containerHTML;
	private $bodyHTML;
	var $view;
	
	function getElementArray() {
		return $elementArray;
	}
	*/
	function buildPage() {

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
		

		$footerElement = new Element($this->pageViewReference);
		
		$headerElement = new Element($this->pageViewReference);
		
		$headerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/body_test.html'));
		
		array_push($this->elementArray, $headerElement);
		
		$contentElement = new Element($this->pageViewReference);
		
		// Content element HTML
		$HTML = <<<HTML
		<div class="copy_block">
				<h1>404</h1>
				<h2>That release is so obscure, it doesn't even exist.</h2>
				<p><a href="/">Back to the comfortable banality of the mainstream Centagon Records home page.</a></p>
				<p>Don't cry. There's always this song.</p>
				<iframe width="420" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allowfullscreen></iframe>
		</div>
HTML;
		
		$contentElement->setHTML($HTML);
		array_push($this->elementArray, $contentElement);

		$videoElement = new Element($this->pageViewReference);
		
		$videoElement->setHTML('<div class="copy_block"><iframe width="420" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allowfullscreen></iframe></div>');
		//array_push($this->elementArray, $videoElement);
		
		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));
		
		array_push($this->elementArray, $footerElement);

		return $this->elementArray;
		
	}
	
}

?>