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

		$pageAccessToken = '';
		$userId = 0;

		// User logged in?
		if ($this->pageViewReference->sessionManager->adminSessionActive == true)
			$userId = $this->pageViewReference->sessionManager->userId;

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
		LEFT OUTER JOIN
			toonces.page_user_access pua ON bpgs.page_id = pua.page_id and pua.user_id = %d
		WHERE
			pgs.page_id = %d
		AND
			bp.deleted IS NULL
		AND
			(
				(bpgs.published = TRUE)
				OR
				(%s)
			)
		ORDER BY
			 bp.created_dt DESC
			,bp.blog_post_id DESC
		;
SQL;


		// if page is published, display post.
		// If page is not published:
		// 	If user is admin, display post.
		// 	If user has access, display post.

		if ($this->pageViewReference->sessionManager->userIsAdmin == true) {
			$pageAccessToken = '1 = 1';
		} else {
			$pageAccessToken = 'pua.page_id IS NOT NULL';
		}

		$pageIdQuery = sprintf($sql,$userId,$this->blogPageId,$pageAccessToken);

		return $pageIdQuery;

	}

}
