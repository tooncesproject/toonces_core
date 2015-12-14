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
 */


{
	private $conn;
	var $blogPageId;
	var $pageNumber;
	var $itemsPerPage;
	var $postIdString;
	var $postCount;
	
	//construct method
	public function __construct($pageView) {
		//$this->conn = UniversalConnect::doConnect;
		
		$this->conn = UniversalConnect::doConnect();
		// $this->theBlogId = $blogId;
		$this->pageViewReference = $pageView;
		$this->blogPageId = $this->pageViewReference->pageId;	
	}

	function queryBlog() {
		
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
		
		// run query to get a list of all the blog post ids
		$postIdResults = $this->conn->query($pageIdQuery);
		
		// populate an array of the results.
		$allPostIds = array();
		foreach ($postIdResults as $resultRow) {
			array_push($allPostIds,$resultRow['blog_post_id']);
		}
		$this->postCount = count($allPostIds);
		
		// build a concatenated list of blog post ids per the input parameters
		$postOrdinal= 1;
		$postIdSet = array();
		$minPost = $this->itemsPerPage * $this->pageNumber - $this->itemsPerPage + 1;
		$maxPost = $this->itemsPerPage * $this->pageNumber;
		
		// if the query string specifies a set of blog posts that doesn't exist, default to
		// the most recent set.
		
		if ($minPost > $this->postCount) {
			
			$maxPost = min($this->itemsPerPage,$this->postCount);
			$minPost = 1;

		}
		

		foreach ($allPostIds as $postIdRow) {
			
			if ($postOrdinal >= $minPost and $postOrdinal <= $maxPost) {
				array_push($postIdSet, strval($postIdRow));
			}
			$postOrdinal++;
			if ($postOrdinal > $maxPost) {
				break;
			}
		}
		
		$postIdString = implode(',',$postIdSet);
		
		$blogPostQuery = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts.sql'),$postIdString);
		
		$blogPostResult = $this->conn->query($blogPostQuery);
		
		return $blogPostResult;
		
		
	}
	
	
	function makeSimpleNavigator($postCount) {
		
		$navHTML = '';
		
		// If there are any newer posts, generate a link to the next page
		if ($postCount > $this->pageNumber * $this->itemsPerPage) {
			$nextPageUrl = $_SERVER['HTTP_HOST'].$_SERVER['PATH'].'?page='.strval($this->pageNumber + 1).'&itemsperpage='.strval($this->itemsPerPage);
			$navHTML = $navHTML.'<div class="nextpage"><a href="'.$nextPageUrl.'">Newer</a></div>'.PHP_EOL; 
		}
			
			// If the page is greater than 1, generate a link to the previous page
		if ($this->pageNumber > 1) {
			$previousPageUrl = $_SERVER['HTTP_HOST'].$_SERVER['PATH'].'?page='.strval($this->pageNumber - 1).'&itemsperpage='.strval($this->itemsPerPage);
			$navHTML = $navHTML.'<div class="prevpage"><a href="'.$previousPageUrlPageUrl.'">Older</a></div>'.PHP_EOL;
		}
		
		return $navHTML;
	}

	
	public function getHTML() {
		
		$html = '<div class="blogreader">'.PHP_EOL;
				
		$queryRows = $this->queryBlog();
		
		// row contains: created_dt, author, title, body
		
		foreach($queryRows as $row) {
			
			$postPageId = $row['page_id'];
			$postPageURL = GrabPageURL::getURL($postPageId);
			$html = $html.'<p><h1><a href="'.$postPageURL.'">'.$row['title'].'</a></h1></p>'.PHP_EOL;
			$html = $html.'<p><h2>'.$row['author'].'</h2></p>'.PHP_EOL;
			$html = $html.'<p>'.$row['created_dt'].'</p>'.PHP_EOL;
			$html = $html.'<p><body>'.$row['body'].'</body></p>'.PHP_EOL;
		}
		
		$html = $html.'</div>'.PHP_EOL;
		
		$html = $html.$this->makeSimpleNavigator($this->postCount);
		
		$this->conn = null;
		return $html;
		
	}
}