<?php

include_once ROOTPATH.'/toonces.php';

class BlogReader implements iElement

{
	private $conn;
	var $query;
	var $theBlogId;
	var $pageViewReference;
	//var $paramArray = array();
	
	// specific to this class, with defaults:
	var $pageNumber;
	var $itemsPerPage;
	
	//construct method
	public function __construct($pageView, $blogId) {
		//$this->conn = UniversalConnect::doConnect;
		
		$this->conn = UniversalConnect::doConnect();
		$this->theBlogId = $blogId;
		$this->pageViewReference = $pageView;
	}

	function queryBlog() {
		
		// get parameters from pageview query string
		$itemsPerPage = intval($this->pageViewReference->queryArray['itemsperpage']);
		$pageNumber = intval($this->pageViewReference->queryArray['page']);
		
		// Check to see if they are set; if not, use defaults.
		if (!is_int($itemsPerPage)) {
			$itemsPerPage = 10;
		}
		
		if (!is_int($pageNumber)) {
			$pageNumber = 1;
		}
		
		//$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts.sql'),$this->theBlogId);
		$query = sprintf('call toonces.sp_get_blog_posts(%d,%d,%d)',$this->theBlogId,$this->itemsPerPage,$this->pageNumber);
		
		//$result = $this->conn->query($query);
		
		$result = $this->conn->query($query);
		
		return $result;
		
		//return $this->runQuery($this->conn, $query);
		
		
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