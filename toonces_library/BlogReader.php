<?php

include_once ROOTPATH.'/static_classes/SQLConn.php';
include_once ROOTPATH.'/utility/UniversalConnect.php';

class BlogReader implements iElement

{
	private $conn;
	var $query;
	
	//construct method
	public function __construct() {
		//$this->conn = UniversalConnect::doConnect;
		
		$this->conn = UniversalConnect::doConnect();
		
	}
	
	function queryBlog() {
		
		$query = file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts.sql');
		
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
			
			
			$html = $html.'<p><h1>'.$row['title'].'</h1></p>'.PHP_EOL;
			$html = $html.'<p><h2>'.$row['author'].'</h2></p>'.PHP_EOL;
			$html = $html.'<p>'.$row['created_dt'].'</p>'.PHP_EOL;
			$html = $html.'<p><body>'.$row['body'].'</body></p>'.PHP_EOL;
		}
		
		$html = $html.'</div>'.PHP_EOL;
		
		$this->conn = null;
		return $html;
		
	}
}