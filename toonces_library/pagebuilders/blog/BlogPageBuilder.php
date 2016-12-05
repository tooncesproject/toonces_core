<?php
/*
 * BlogPageBuilder
 * Initial commit: Paul Anderson, 5/30/2016
 * 
 * An extension of StandardPageBuilder for a blog home page.
 * 
 */

 require_once LIBPATH.'toonces.php';
 
 class BlogPageBuilder extends StandardPageBuilder
 {
 
 	function createContentElement() {
 		
 		// Check for edit mode signal from GET, and if applicable, check for user access.
 		$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
 		
 		if ($mode == 'newpost' and $this->pageViewReference->userCanEdit == true) {
 			$blogFormElement = new BlogFormElement($this->pageViewReference);
 			$this->contentElement = $blogFormElement;
 		} else {
 			$blogReader = new BlogPageReader($this->pageViewReference);
 			$this->contentElement = $blogReader;
 		}
 		
 	}
 }