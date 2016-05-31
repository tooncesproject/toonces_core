<?php
/*
 * BlogPostCreatorPageBuilder
 * Fucking useless
 * 
 */

require_once LIBPATH.'toonces.php';

class BlogPostCreatorPageBuilder extends StandardPageBuilder
{
	function createContentElement() {

		$this->contentElement = new BlogFormElement($this->pageViewReference);

	}
}