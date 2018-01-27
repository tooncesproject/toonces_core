<?php
/*
 * BlogPostSinglePageBuilder
 * Extends StandardPageBuilder to display a single blog post.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class BlogPostSinglePageBuilder extends StandardPageBuilder
{
	function createContentElement() {

		// Check for edit mode signal from GET, and if applicable, check for user access.
		$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';

		// If user doesn't have editing capability, ignore the mode.
		if (!$this->pageViewReference->checkUserCanEdit) {
			$mode = '';
		}
		switch ($mode) {
			case 'edit':
				$blogEditorFormElement = new BlogEditorFormElement($this->pageViewReference);
				$this->contentElement = $blogEditorFormElement;
				break;
			case 'urlcheck':
				$urlCheckFormElement = new URLCheckFormElement($this->pageViewReference);
				$this->contentElement = $urlCheckFormElement;
				break;
			case 'delete':
				$deleteBlogPostFormElement = new DeleteBlogPostFormElement($this->pageViewReference);
				$this->contentElement = $deleteBlogPostFormElement;
				break;
			default:
				$blogReaderSingle = new BlogReaderSingle($this->pageViewReference);
				$this->contentElement = $blogReaderSingle;
		}

	}
}