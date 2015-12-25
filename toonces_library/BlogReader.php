<?php

include_once ROOTPATH.'/toonces.php';

class BlogReader implements iElement

{
	private $conn;
	var $query;
	var $blogIdString;
	var $pageViewReference;
	//var $paramArray = array();
	
	// specific to blog reader:
	var $blogPageId;
	var $pageNumber;
	var $itemsPerPage;
	var $postIdString;
	var $postCount;
	public $olderLinkText;
	public $newerLinkText;
	
	//construct method
	public function __construct($pageView) {
		//$this->conn = UniversalConnect::doConnect;
		
		$this->conn = UniversalConnect::doConnect();
		$this->pageViewReference = $pageView;
		$this->blogPageId = $this->pageViewReference->pageId;
	}
	
	// Explicit blog ID setter method, because it's required.
	function setBlogId($blogId) {
		$this->blogIdString = strval($blogId);
	}
	
	function setMultiBlogIds($blogIdArray) {
		$this->blogIdString = implode(',',$blogIdArray);
	}
	
	function buildPageIdQuery() {
		
		if (!isset($this->blogIdString)) {
			
			throw new Exception('blog ID string not set');
			
		} else {
			
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
			$pageIdQuery = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_post_ids_by_blog_id.sql'),strval($this->blogIdString));
		}
		
		return $pageIdQuery;
	}

	function queryBlog() {
	
		$pageIdQuery = $this->buildPageIdQuery();
		
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
		
		// No blog posts yet? Make it return an empty set.
		if (empty($postIdSet)) {
			array_push($postIdSet,0);
		}
		
		$postIdString = implode(',',$postIdSet);
	
		$blogPostQuery = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_blog_posts.sql'),$postIdString);
		$blogPostResult = $this->conn->query($blogPostQuery);
	
		return $blogPostResult;
	
	}

	function makeSimpleNavigator($postCount) {
		
		// set link text to defaults if not set
		if (!isset($this->olderLinkText)) {
			$this->olderLinkText = 'Older Posts';
		}
		
		if (!isset($this->newerLinkText)) {
			$this->newerLinkText = 'Newer Posts';
		}
	
		$navHTML = '<div class="bottom_nav_container">'.PHP_EOL;
	
		// If the page is greater than 1, generate a link to the previous page
		if ($this->pageNumber > 1) {
			$previousPageUrl = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH).'?page='.strval($this->pageNumber - 1).'&itemsperpage='.strval($this->itemsPerPage);
			$navHTML = $navHTML.'<div class="prev_page_link"><a href="'.$previousPageUrl.'">'.$this->newerLinkText.'</a></div>'.PHP_EOL;
		}
	
		// If there are any newer posts, generate a link to the next page
		if ($postCount > $this->pageNumber * $this->itemsPerPage) {
			$nextPageUrl = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH).'?page='.strval($this->pageNumber + 1).'&itemsperpage='.strval($this->itemsPerPage);
			$navHTML = $navHTML.'<div class="next_page_link"><a href="'.$nextPageUrl.'">'.$this->olderLinkText.'</a></div>'.PHP_EOL;
		}
			
		$navHTML = $navHTML.'</div>';
	
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
		
		$html = $html.$this->makeSimpleNavigator($this->postCount);
		$html = $html.'</div>'.PHP_EOL;
		
		$this->conn = null;
		return $html;
		
	}
}