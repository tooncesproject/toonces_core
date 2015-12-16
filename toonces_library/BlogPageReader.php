<?php

require_once ROOTPATH.'/toonces.php';

class BlogPageReader extends BlogReader implements iElement

/*
 * 	Paul Anderson 10/10/2015
 * 	This extension of the Blog Reader class acquires the blog content
 * 	by the blog's home page ID, where BlogReader gets it from the BlogId.
 * 	
 *	Because BlogPageReader queries for content based on the page ID, it's
 *	specifically designed to be instantiated in a blog home page, where
 *	BlogReaader is designed to be used outside the blog's home page.
 *
 * This class overrides buildPageIdQuery(). All other functions are inherited.
 * 
 */


{
/* inherited instance variables commented out
	private $conn;
	var $blogPageId;
	var $pageNumber;
	var $itemsPerPage;
	var $postIdString;
	var $postCount;
*/

	function buildPageIdQuery() {

			// get parameters from pageview query string, if they exist
		if (array_key_exists('itemsperpage', $this->pageViewReference->queryArray)) {
			$this->itemsPerPage = intval($this->pageViewReference->queryArray['itemsperpage']);
		}
		if (array_key_exists('page',$this->pageViewReference->queryArray))
			$this->pageNumber = intval($this->pageViewReference->queryArray['page']);
		
		// Check to see if they are set; if not, use defaults.
		if (!is_int($this->itemsPerPage)) {
			$this->itemsPerPage = 10;
		}
		
		if (!is_int($this->pageNumber)) {
			$this->pageNumber = 1;
		}
		
		$pageIdQuery = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_post_ids_by_page_id.sql'),$this->blogPageId);
		
		return $pageIdQuery;
		
	}

}
