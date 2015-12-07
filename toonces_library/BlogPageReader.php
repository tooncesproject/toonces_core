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
	var $query;
	var $theBlogPageId;
	var $pageNumber;
	var $itemsPerPage;
	var $postIdString;
	
	//construct method
	public function __construct($pageView) {
		//$this->conn = UniversalConnect::doConnect;
		
		$this->conn = UniversalConnect::doConnect();
		// $this->theBlogId = $blogId;
		$this->pageViewReference = $pageView;
		$this->theBlogPageId = $this->pageViewReference->pageId;	
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
		
		// query the SQL function to get desired blog post ids
		// it doesn't work.
		//$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts.sql'),$this->postIdString);
		$query = sprintf('CALL toonces.sp_get_blog_posts(%d,%d,%d)',$this->theBlogId,$this->itemsPerPage,$this->pageNumber);
		
		
		$result = $this->conn->query($query);
		
		return $result;
		
		
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
		
		$this->conn = null;
		return $html;
		
	}
}