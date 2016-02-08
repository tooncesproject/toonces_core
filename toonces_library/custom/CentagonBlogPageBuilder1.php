<?php



class CentagonBlogPageBuilder1 extends PageBuilder {
	/*
	var $elementArray = array();
	private $containerHTML;
	private $bodyHTML;
	var $view;

	function getElementArray() {
		return $elementArray;
	}

	*/
	var $displayElement;

	function buildPage() {

		// Check for edit mode signal from GET, and if applicable, check for user access.
		if (isset($_GET['mode'])) {
			if ($_GET['mode'] == 'newpost' and $this->pageViewReference->userCanEdit == true) {
				//placeholder...
				$blogPostFormElement = new Element($this->pageViewReference);
				$blogPostFormElement->setHTML('Hello, i\'m a placeholder for blog form element.');
				$this->displayElement = $blogPostFormElement;
			}
		} else {
			$blogReader = new BlogPageReader($this->pageViewReference);
			$this->displayElement = $blogReader;
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
		$pageHeader->setHTML(file_get_contents(ROOTPATH.'/static_data/centagon_header.html'));
		array_push($this->elementArray, $pageHeader);

		$pageId = $this->pageViewReference->pageId;

		//$blogReader = new BlogPageReader($this->pageViewReference);
		// Insert the element you wanna use here...
		//array_push($this->elementArray, $blogReader);
		array_push($this->elementArray, $this->displayElement);

		$footerElement = new Element($this->pageViewReference);

		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));

		array_push($this->elementArray, $footerElement);

	}


}

?>