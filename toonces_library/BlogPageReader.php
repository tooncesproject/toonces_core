<?php

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

class BlogPageReader extends BlogReader implements iElement

{
	function buildPageIdQuery() {

		$publishedTrigger = '';

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

		$sql = <<<SQL
		SELECT
			 bp.blog_post_id
		FROM
			toonces.blogs blg
		JOIN
			toonces.pages pgs ON blg.page_id = pgs.page_id
		JOIN
			toonces.blog_posts bp ON blg.blog_id = bp.blog_id
		JOIN
			toonces.pages bpgs ON bp.page_id = bpgs.page_id
		WHERE
			pgs.page_id = %d
		AND
			(bpgs.published = %s)
		ORDER BY
			bp.created_dt DESC;
SQL;

		// If the user is logged in and has editing capabilities,
		// display all posts. Otherwise, only display published posts.
		if ($this->pageViewReference->userCanEdit == true) {
			$publishedTrigger = 'TRUE OR 1 = 1';
		} else {
			$publishedTrigger = 'TRUE';
		}

		$pageIdQuery = sprintf($sql,$this->blogPageId,$publishedTrigger);

		return $pageIdQuery;

	}

}
