<?php

include_once ROOTPATH.'/ViewElement.php';
include_once ROOTPATH.'/Element.php';
include_once ROOTPATH.'/abstract/PageBuilder.php';
include_once ROOTPATH.'/BlogReader.php';
include_once ROOTPATH.'/DivElement.php';
include_once ROOTPATH.'/TagElement.php';
include_once ROOTPATH.'/HeadElement.php';
include_once ROOTPATH.'/PageView.php';
include_once ROOTPATH.'/BlogReaderSingle.php';

class CentagonBlogPostTest extends PageBuilder {
	/*
	var $elementArray = array();
	private $containerHTML;
	private $bodyHTML;
	var $view;
	
	function getElementArray() {
		return $elementArray;
	}
	*/
	function buildPage($pageView) {
		
		
		$this->pageViewReference = $pageView;
		
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
		
		$bodyElement = new Element($this->pageViewReference);
		
		$bodyElement->setHTML(file_get_contents(ROOTPATH.'/static_data/body_test.html'));
		
		array_push($this->elementArray, $bodyElement);
		
		$pageId = $this->pageViewReference->pageId;
		
		$blogReaderSingle = new BlogReaderSingle($pageId);
		
		array_push($this->elementArray, $blogReaderSingle);
		
		$footerElement = new Element($this->pageViewReference);
		
		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));
		
		array_push($this->elementArray, $footerElement);
		
		return $this->elementArray;
		
	}
	
}

?>