<?php

include_once ROOTPATH.'/static_classes/SQLConn.php';
include_once ROOTPATH.'/utility/UniversalConnect.php';
include_once ROOTPATH.'/static_classes/GrabPageURL.php';

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
	
	//construct method
	public function __construct($blogPageId) {
		
		$this->conn = UniversalConnect::doConnect();
		$this->theBlogPageId = $blogPageId;

	}

	function queryBlog() {
		
		$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts_by_blog_page_id.sql'),$this->theBlogPageId);
		
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