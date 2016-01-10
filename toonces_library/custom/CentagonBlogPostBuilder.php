<?php



class CentagonBlogPostBuilder extends PageBuilder {
	/*
	var $elementArray = array();
	private $containerHTML;
	private $bodyHTML;
	var $view;
	
	function getElementArray() {
		return $elementArray;
	}
	
	*/
	var $blogPageReader;
	
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
		
		$blogFormElement = new BlogFormElement($this->pageViewReference->pageId);
		
		
		$blogFormElement->blogId = '1';
		$blogFormElement->pageBuilderClass = 'CentagonBlogPageBuilder1';
		
		array_push($this->elementArray, $blogFormElement);
		
		$footerElement = new Element($this->pageViewReference);
		
		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));
		
		array_push($this->elementArray, $footerElement);
		
		return $this->elementArray;
		
	}
	
}

?>