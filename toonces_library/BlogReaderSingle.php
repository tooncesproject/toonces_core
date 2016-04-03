<?php

require_once ROOTPATH.'/toonces.php';

class BlogReaderSingle extends Element implements iElement

{
	private $conn;
	var $query;
	var $pageId;
	var $blogPostId;
	var $blogId;
	var $createdDT;


	function queryBlog() {

		if (!isset($this->conn))
			$this->conn = UniversalConnect::doConnect();

		$query = <<<SQL
		SELECT
			 bp.created_dt
			,u.nickname AS author
			,bp.title
			,bp.body
			,bp.page_id
			,bp.blog_post_id
			,bp.blog_id
		FROM
			toonces.blog_posts bp
		JOIN
			toonces.users u USING (user_id)
		WHERE
			bp.page_id = %d
			;
SQL;

		$query = sprintf($query,$this->pageId);
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
		$userId = 0;
		$userIsAdmin = false;

		if (isset($this->conn) == false)
			$this->conn = UniversalConnect::doConnect();

		// Is the user logged in? Are they an admin?
		$adminSessionActive = $this->pageViewReference->sessionManager->adminSessionActive;
		if ($adminSessionActive) {
			$userId = $this->pageViewReference->sessionManager->userId;
			$userIsAdmin = $this->pageViewReference->sessionManager->userIsAdmin;
		}

		$queryParams = array(
				 ':userId' => $userId
				,':createdDT' => $this->createdDT
				,':blogPostId' => $this->blogPostId
		);

		// Query for previous pages 
		$sql = <<<SQL
				SELECT
			 bp.blog_post_id
			,p.page_id
			,GET_PAGE_PATH(p.page_id) AS pagepath
			,bp.created_dt
			,CASE WHEN
				p.published = 1
				AND
				bp.published = 1
			 THEN 1 ELSE 0 END AS guest_ok
			,CASE WHEN
				(p.published = 1 AND bp.published = 1)
				OR
				pua.page_id IS NOT NULL
			 THEN 1 ELSE 0 END AS user_ok
		FROM
			blog_posts bp
		JOIN
			pages p ON bp.page_id = p.page_id
		LEFT OUTER JOIN
			page_user_access pua ON bp.page_id = pua.page_id AND pua.user_id = :userId
		WHERE
			bp.created_dt <= :createdDT
		AND
			bp.blog_post_id != :blogPostId
		AND
			bp.deleted IS NULL
		AND
			p.deleted IS NULL
		ORDER BY
			 bp.created_dt DESC
			,bp.blog_post_id DESC
SQL;

		$stmt = $this->conn->prepare($sql);
		$stmt->execute($queryParams);
		$prevPageResult = $stmt->fetchAll();


		foreach ($prevPageResult as $row) {
			$checkPage = true;
			// Skip the page if it has the same created timestamp as the one we're on but a higher blog post ID.
			if ($row['created_dt'] = $this->createdDT && $row['blog_post_id'] > $this->blogPostId)
				$checkPage = false;

			if ($checkPage) {
				// If user is admin, break and exit.
				if ($userIsAdmin == true) {
					$previousPagePath = $row['pagepath'];
					$previousPageId = $row['page_id'];
					break;
				}
				// If user is non-admin but logged in and link is viewable by this user break and exit.
				if ($userId != 0 and $row['user_ok'] == 1) {
					$previousPagePath = $row['pagepath'];
					$previousPageId = $row['page_id'];
					break;
				}
				// If user is guest and link is viewable to guest users, break and exit
				if ($row['guest_ok'] == 1) {
					$previousPagePath = $row['pagepath'];
					$previousPageId = $row['page_id'];
					break;
				}
			}
		}

		// Query for next pages
		$sql = <<<SQL
		SELECT
			 bp.blog_post_id
			,p.page_id
			,GET_PAGE_PATH(p.page_id) AS pagepath
			,bp.created_dt
			,CASE WHEN
				p.published = 1
				AND
				bp.published = 1
			 THEN 1 ELSE 0 END AS guest_ok
			,CASE WHEN
				(p.published = 1 AND bp.published = 1)
				OR
				pua.page_id IS NOT NULL
			 THEN 1 ELSE 0 END AS user_ok
		FROM
			blog_posts bp
		JOIN
			pages p ON bp.page_id = p.page_id
		LEFT OUTER JOIN
			page_user_access pua ON bp.page_id = pua.page_id AND pua.user_id = :userId
		WHERE
			bp.created_dt >= :createdDT
		AND
			bp.blog_post_id != :blogPostId
		AND
			bp.deleted IS NULL
		AND
			p.deleted IS NULL
		ORDER BY
			 bp.created_dt ASC
			,bp.blog_post_id ASC
SQL;

		$stmt = $this->conn->prepare($sql);
		$stmt->execute($queryParams);
		$nextPageResult = $stmt->fetchAll();

		foreach ($nextPageResult as $row) {
			$checkPage = true;
			// Skip the page if it has the same created timestamp as the one we're on but a higher blog post ID.
			if ($row['created_dt'] = $this->createdDT && $row['blog_post_id'] < $this->blogPostId)
				$checkPage = false;

			if ($checkPage) {
				// If user is admin, break and exit.
				if ($userIsAdmin == true) {
					$nextPagePath = $row['pagepath'];
					$nextPageId = $row['page_id'];
					break;
				}
				// If user is non-admin but logged in and link is viewable by this user break and exit.
				if ($userId != 0 and $row['user_ok'] == 1) {
					$nextPagePath = $row['pagepath'];
					$nextPageId = $row['page_id'];
					break;
				}
				// If user is guest and link is viewable to guest users, break and exit
				if ($row['guest_ok'] == 1) {
					$nextPagePath = $row['pagepath'];
					$nextPageId = $row['page_id'];
					break;
				}
			}
		}

		// Build navigation html
		$navHTML = '<div class="bottom_nav_container">'.PHP_EOL;

		if ($previousPageId != 0)
			$previousHTML = '<div class="prev_page_link"><a href="'.$previousPagePath.'">Previous Post</a></div>'.PHP_EOL;

		if ($nextPageId != 0)
			$nextHTML = '<div class="next_page_link"><a href="'.$nextPagePath.'">Next Post</a></div>'.PHP_EOL;

		$parentURL = GrabParentPageURL::getURL($this->pageViewReference->pageId);


		$navHTML = $navHTML.$previousHTML.$nextHTML.'</div>'.PHP_EOL;

		$parentURL = GrabParentPageURL::getURL($this->pageViewReference->pageId);
		$parentLink = '<div class="bottom_nav_container">'.PHP_EOL.'<div class="parent_page_link"><a href="'.$parentURL.'">Back to the Blog</a></div></div>'.PHP_EOL;
		$navHTML = $navHTML.$parentLink;

		return $navHTML;

	}


	public function getHTML() {

		if (!isset($this->conn))
			$this->conn = UniversalConnect::doConnect();

		$this->pageId = $this->pageViewReference->pageId;
		$title = '';
		$author = '';
		$body = '';

		$html = '<div class="blogreader">'.PHP_EOL;

		$queryRows = $this->queryBlog();

		// row contains: created_dt, author, title, body

		foreach($queryRows as $row) {

			$postPageId = $row['page_id'];
			$title = $row['title'];
			$author = $row['author'];
			$dateFormatted = date('l, F j, Y g:i:s', strtotime($row['created_dt']));
			$this->createdDT = $row['created_dt'];
			$body = $row['body'];
			$this->blogPostId = $row['blog_post_id'];
			$this->blogId = $row['blog_id'];

		}

		// process strings
		$body = trim(preg_replace('/\n+/', '<br>', $body));

		$html = $html.'<p><h1>'.$title.'</h1></p>'.PHP_EOL;
		$html = $html.'<p><h2>'.$author.'</h2></p>'.PHP_EOL;
		$html = $html.'<p>'.$dateFormatted.'</p>'.PHP_EOL;
		$html = $html.'<p><body>'.$body.'</body></p>'.PHP_EOL;

		$html = $html.'</div>'.PHP_EOL;

		//Add navigation
		$navigationHTML = $this->generateNavigation();
		$html = $html.$navigationHTML;

		return $html;

	}

}
