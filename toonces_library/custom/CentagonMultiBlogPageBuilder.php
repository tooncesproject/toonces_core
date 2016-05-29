<?php



class CentagonMultiBlogPageBuilder extends PageBuilder {

	var $blogPageReader;

	function buildPage() {

		// get static/generic html header, create as element
		$htmlHeaderElement = new Element($this->pageViewReference);
		$htmlHeaderElement->setHTML(file_get_contents(LIBPATH.'static_data/generic_html_header.html'));

		array_push($this->elementArray, $htmlHeaderElement);

		$headElement = new HeadElement($this->pageViewReference);

		// get head attributes
		$headElement->setPageTitle($this->pageViewReference->getPageTitle());
		$headElement->setStyleSheet($this->pageViewReference->getStyleSheet());

		$headElement->setHeadTags(file_get_contents(LIBPATH.'static_data/head_tags.html'));

		array_push($this->elementArray, $headElement);

		$bodyElement = new Element($this->pageViewReference);

		$bodyElement->setHTML(file_get_contents(LIBPATH.'static_data/body_test.html'));

		array_push($this->elementArray, $bodyElement);

		$pageId = $this->pageViewReference->pageId;

		$blogReader = new BlogReader($this->pageViewReference);

		$multiBlogArray = array();
		array_push($multiBlogArray, 1);
		array_push($multiBlogArray, 2);
		$blogReader->setMultiBlogIds($multiBlogArray);

		array_push($this->elementArray, $blogReader);

		$footerElement = new Element($this->pageViewReference);

		$footerElement->setHTML(file_get_contents(LIBPATH.'static_data/real_footer_ish.html'));

		array_push($this->elementArray, $footerElement);

		return $this->elementArray;

	}

}

?>