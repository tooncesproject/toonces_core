<?php

require_once LIBPATH.'php/toonces.php';

class HTMLPageView extends HTMLViewResource implements iHTMLView, iPageView
{
	// instance variables

	var $htmlHeader;
	var $pageElements = array();
	var $pageTitle;
	var $styleSheet;
	var $pageLinkText;
	var $pageId;
	var $queryArray = array();
	var $sessionManager;
	var $logoutSignal;
	var $userCanEdit;
	var $userCanAccessAdminPage;
	var $pageURI;
	var $pageIsPublished;
	var $sqlConn;
	var $adminSessionActive;

	public function __construct($pageViewPageId) {
		$this->pageId = $pageViewPageId;
		parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
	}

	// Setters & getters for compliance to iHTMLView
	public function setPageTitle($paramPageTitle) {
		$this->pageTitle = $paramPageTitle;
	}

	public function getPageTitle() {
		return $this->pageTitle;
	}

	public function setPageIsPublished($paramPageIsPublished) {
		$this->pageIsPublished = $paramPageIsPublished;
	}

	public function getPageIsPublished() {
		return $this->pageIsPublished;
	}

	public function setResource ($element) {
		array_push($this->pageElements,$element);
		$this->elementsCount++;
	}

	public function checkUserCanEdit() {
	    return $this->userCanEdit;
	}


	public function setSQLConn($paramSQLConn) {
		$this->sqlConn = $paramSQLConn;
	}

	public function getSQLConn() {
		return $this->sqlConn;
	}

	public function setPageLinkText($paramPageLinkText) {
		$this->pageLinkText = $paramPageLinkText;
	}

	public function getPageLinkText() {
		return $this->pageLinkText;
	}

	// execution methods
	public function checkSessionAccess() {
	
		$userId = 0;
		$userIsAdmin = false;
		$isAdminPage = false;
		$pageIsPublished = false;
		$userHasPageAccess = false;
		$this->userCanEdit = false;
		$allowAccess = false;

		// Instantiate session manager and check status.
		if (!$this->sessionManager)
		   $this->sessionManager = new SessionManager($this->sqlConn);
		$this->sessionManager->checkSession();
	
		$this->adminSessionActive = $this->sessionManager->adminSessionActive;

		if ($this->adminSessionActive) {
			$userId = $this->sessionManager->userId;
			$userIsAdmin = $this->sessionManager->userIsAdmin;
		}
		// Query the database for publication and user access state
		$sql = <<<SQL
			SELECT
				 p.published
				,CASE
					WHEN pua.page_id IS NOT NULL THEN 1
					ELSE 0
				END AS user_has_access
				,COALESCE(pua.can_edit,0) AS can_edit
			FROM
				toonces.pages p
			LEFT OUTER JOIN
				toonces.page_user_access pua
					ON p.page_id = pua.page_id
					AND pua.user_id = :userId
			WHERE
				p.page_id = :pageId;

SQL;
	
		$stmt = $this->sqlConn->prepare($sql);
		$stmt->execute(array(':userId' => $userId, ':pageId' => $this->pageId));
		$result = $stmt->fetchAll();
		$row = $result[0];
		$this->pageIsPublished = $row['published'];
		$userHasPageAccess = $row['user_has_access'];
		// Admin user can always edit
		$this->userCanEdit = ($userIsAdmin) ? true : $row['can_edit'];

		// Is the page unpublished?
		if (!$this->pageIsPublished) {
			// Allow access to page if:
			// user is admin
			if ($userIsAdmin)
				$allowAccess = true;

			// user is logged in and has access
			if ($userHasPageAccess )
				$allowAccess = true;

		} else {
			// If published, page is public.
			$allowAccess = true;
		}

		return $allowAccess;
	
	}

	public function checkAdminSession() {
		$this->checkSessionAccess();
		return $this->adminSessionActive;
	}
	public function getResource() {

		$htmlString = $this->htmlHeader;

		foreach($this->pageElements as $object) {
			$htmlString = $htmlString.$object->getResource();
		}

		return $htmlString;
	}

	public function renderResource() {

		echo $this->getResource();

	}

}

