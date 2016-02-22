<?php

require_once ROOTPATH.'/toonces.php';

class CentagonBlogPostSingle extends PageBuilder {
	
	var $blogPostId;

	function buildPage() {
		
		$postContent = '';
		$title = '';
		$body = '';
	
		// Instantiate the BlogReaderSingle object and get its contents.
		$blogReaderSingle = new BlogReaderSingle($this->pageViewReference);

		// Check for edit mode signal from GET, and if applicable, check for user access.
		$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
		
		if ($mode == 'edit' and $this->pageViewReference->userCanEdit == true) {
			$result = $blogReaderSingle->queryBlog();
			
			foreach($result as $row) {

				$title = $row['title'];
				$body = $row['body'];
				$this->blogPostId = $row['blog_post_id'];
			
			}
			$blogEditorFormElement = new BlogEditorFormElement($this->pageViewReference);
			$blogEditorFormElement->textareaValue = $body;
			$blogEditorFormElement->blogPostTitle = $title;
			
			$this->displayElement = $blogEditorFormElement;
		} else {
			$this->displayElement = $blogReaderSingle;
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
	
		array_push($this->elementArray, $this->displayElement);
	
		$footerElement = new Element($this->pageViewReference);
	
		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/real_footer_ish.html'));
	
		array_push($this->elementArray, $footerElement);
	
	}
}

?>