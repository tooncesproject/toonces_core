<?php

require_once ROOTPATH.'/toonces.php';

class BlogReaderSingle extends Element implements iElement

{
	private $conn;
	var $query;
	var $pageId;
	var $blogPostId;


	function queryBlog() {

		$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_single_blog_post.sql'),$this->pageId);

		$result = $this->conn->query($query);

		return $result;

	}

	function generateNavigation() {

		$previousPageId = 0;
		$previousPagePath = '';
		$nextPageId = 0;
		$nextPagePath = '';
		$navHTML = '';
		$previousHTML = '';
		$nextHTML = '';

		// Is the user logged in?
		$adminSessionActive = $this->pageViewReference->sessionManager->adminSessionActive;

		if ($adminSessionActive == true) {
			// If so, is the user an admin user?
			if ($this->pageViewReference->sessionManager->userIsAdmin == true) {
				$sql = $this->adminUserNavQuery($this->blogPostId);
			} else {
				$userId = $this->pageViewReference->sessionManager->userId;
				$sql = $this->loggedInUserNavQuery($this->blogPostId, $userId);
			}
		} else {
			$sql = $this->guestUserNavQuery($this->blogPostId);
		}

		// Execute the query
		if (!isset($this->conn))
			$this->conn = UniversalConnect::doConnect();

		$result = $this->conn->query($sql);

		foreach ($result as $row) {
			if (isset($row['previous_page_id'])) {
				$previousPageId = $row['previous_page_id'];
				$previousPagePath = $row['previous_page_path'];
			}

			if (isset($row['next_page_id'])) {
				$nextPageId = $row['next_page_id'];
				$nextPagePath = $row['next_page_path'];
			}

		}

		// Build navigation html
		$navHTML = '<div class="bottom_nav_container">'.PHP_EOL;

		if ($previousPageId != 0)
			$previousHTML = '<div class="prev_page_link"><a href="'.$previousPagePath.'">Previous Post</a></div>'.PHP_EOL;

		if ($nextPageId != 0)
			$nextHTML = '<div class="next_page_link"><a href="'.$nextPagePath.'">Next Post</a></div>'.PHP_EOL;

		$navHTML = $navHTML.$previousHTML.$nextHTML.'</div>'.PHP_EOL;

		return $navHTML;


	}

	public function getHTML() {

		if (!isset($this->conn))
			$this->conn = UniversalConnect::doConnect();

		$this->pageId = $this->pageViewReference->pageId;
		$title = '';
		$author = '';
		$createdDT = '';
		$body = '';

		$html = '<div class="blogreader">'.PHP_EOL;

		$queryRows = $this->queryBlog();

		// row contains: created_dt, author, title, body

		foreach($queryRows as $row) {

			$postPageId = $row['page_id'];
			$title = $row['title'];
			$author = $row['author'];
			$createdDT = $row['created_dt'];
			$body = $row['body'];
			$this->blogPostId = $row['blog_post_id'];

		}

		// process strings
		$body = trim(preg_replace('/\n+/', '<br>', $body));

		$html = $html.'<p><h1>'.$title.'</h1></p>'.PHP_EOL;
		$html = $html.'<p><h2>'.$author.'</h2></p>'.PHP_EOL;
		$html = $html.'<p>'.$createdDT.'</p>'.PHP_EOL;
		$html = $html.'<p><body>'.$body.'</body></p>'.PHP_EOL;

		$html = $html.'</div>'.PHP_EOL;

		//Add navigation
		$navigationHTML = $this->generateNavigation();
		$html = $html.$navigationHTML;

		return $html;

	}

	function guestUserNavQuery($blogPostID) {

		// Query to get the page IDs of the previous and next posts in the blog.
		$sql = <<<SQL
		SELECT
			 p1.page_id AS previous_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p1.page_id, 0)) AS previous_page_path
			,p2.page_id AS next_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p2.page_id,0)) AS next_page_path
		FROM
			(
				SELECT
					 1 AS joiner
					,MAX(bp.blog_post_id) AS previous_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p USING (page_id)
				WHERE
					bp.published = 1
				AND
					p.published = 1
				AND
					bp.deleted IS NULL
				AND
					bp.blog_post_id < %s
			) prev_post
		JOIN
			(
				SELECT
					 1 AS joiner
					,MIN(bp.blog_post_id) AS next_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p USING (page_id)
				WHERE
					bp.published = 1
				AND
					p.published = 1
				AND
					bp.deleted IS NULL
				AND
					bp.blog_post_id > %s
			) next_post USING (joiner)
		LEFT OUTER JOIN
			toonces.blog_posts p1 ON prev_post.previous_post_id = p1.blog_post_id
		LEFT OUTER JOIN
			toonces.blog_posts p2 ON next_post.next_post_id = p2.blog_post_id;
SQL;

		$sql = sprintf($sql,strval($blogPostID),strval($blogPostID));

		return $sql;

	}

	function loggedInUserNavQuery($blogPostID, $userID) {

		$sql = <<<SQL
		SELECT
			 p1.page_id AS previous_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p1.page_id, 0)) AS previous_page_path
			,p2.page_id AS next_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p2.page_id,0)) AS next_page_path
		FROM
			(
				SELECT
					 1 AS joiner
					,MAX(bp.blog_post_id) AS previous_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p ON bp.page_id = p.page_id
				LEFT OUTER JOIN
					toonces.page_user_access pua ON p.page_id = pua.page_id AND pua.user_id = %s
				WHERE
					bp.blog_post_id < %s
				AND
				(
					(bp.published = 1 AND p.published = 1)
					OR
					pua.page_id IS NOT NULL
				)
			) prev_post
		JOIN
			(
				SELECT
					 1 AS joiner
					,MIN(bp.blog_post_id) AS next_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p ON bp.page_id = p.page_id
				LEFT OUTER JOIN
					toonces.page_user_access pua ON p.page_id = pua.page_id AND pua.user_id = %s
				WHERE
					bp.blog_post_id > %s
				AND
				(
					(bp.published = 1 AND p.published = 1)
					OR
					pua.page_id IS NOT NULL
				)
			) next_post USING (joiner)
		LEFT OUTER JOIN
			toonces.blog_posts p1 ON prev_post.previous_post_id = p1.blog_post_id
		LEFT OUTER JOIN
			toonces.blog_posts p2 ON next_post.next_post_id = p2.blog_post_id;
SQL;

		$sql = sprintf($sql,strval($userID),strval($blogPostID),strval($userID),strval($blogPostID));

		return $sql;
	}

	function adminUserNavQuery($blogPostID) {

		$sql = <<<SQL
		SELECT
			 p1.page_id AS previous_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p1.page_id, 0)) AS previous_page_path
			,p2.page_id AS next_page_id
			,toonces.GET_PAGE_PATH(COALESCE(p2.page_id,0)) AS next_page_path
		FROM
			(
				SELECT
					 1 AS joiner
					,MAX(bp.blog_post_id) AS previous_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p ON bp.page_id = p.page_id
				WHERE
					bp.blog_post_id < %s
			) prev_post
		JOIN
			(
				SELECT
					 1 AS joiner
					,MIN(bp.blog_post_id) AS next_post_id
				FROM
					toonces.blog_posts bp
				JOIN
					toonces.pages p ON bp.page_id = p.page_id
				WHERE
					bp.blog_post_id > %s
			) next_post USING (joiner)
		LEFT OUTER JOIN
			toonces.blog_posts p1 ON prev_post.previous_post_id = p1.blog_post_id
		LEFT OUTER JOIN
			toonces.blog_posts p2 ON next_post.next_post_id = p2.blog_post_id;
SQL;

		$sql = sprintf($sql,strval($blogPostID),strval($blogPostID));

		return $sql;

	}
}