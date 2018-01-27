<?php

require_once LIBPATH.'php/toonces.php';

class PageView extends ViewElement implements iHTMLView, iResourceView
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
	var $pageTypeId;
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

	public function addElement ($element) {
		array_push($this->pageElements,$element);
		$this->elementsCount++;
	}

	// Setters and getters for compliance to iResourceView

	public function setPageURI($paramPageURI) {
		$this->pageURI = $paramPageURI;
	}

	public function getPageURI() {
		return $this->pageURI;
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

	public function setPageTypeID($paramPageTypeID) {
		$this->pageTypeId = $paramPageTypeID;
	}

	public function getPageTypeID() {
	   return $this->pageTypeID;
	}

	// execution methods
	public function checkSessionAccess() {
	
		$userID = 0;
		$userIsAdmin = false;
		$isAdminPage = false;
		$pageIsPublished = false;
		$userHasPageAccess = false;
		$userCanEdit = false;
		$allowAccess = false;

		// Instantiate session manager and check status.
		if (!$this->sessionManager)
		   $this->sessionManager = new SessionManager($this->sqlConn);
		$this->sessionManager->checkSession();
	
		$this->adminSessionActive = $this->sessionManager->adminSessionActive;

		if ($this->adminSessionActive) {
			$userID = $this->sessionManager->userId;
			$userIsAdmin = $this->sessionManager->userIsAdmin;
		}
		// Query the database for publication and user access state
		$sql = <<<SQL
			SELECT
				 p.published
				,p.pagetype_id
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
					AND pua.user_id = :userID
			WHERE
				p.page_id = :pageID;

SQL;
	
		$stmt = $this->sqlConn->prepare($sql);
		$stmt->execute(array(':userID' => $userID, ':pageID' => $this->pageId));
		$result = $stmt->fetchAll();
		$row = $result[0];
		$pageIsPublished = $row['published'];
		$pageTypeId = $row['pagetype_id'];
		$isAdminPage = ($row['pagetype_id'] == 1) ? true : false;
		$userHasPageAccess = $row['user_has_access'];
		// Admin user can always edit
		$userCanEdit = ($userIsAdmin) ? true : $row['can_edit'];

		// Is the page unpublished?
		if (!$pageIsPublished) {
			// Allow access to page if:
			// user is logged in and page is admin page (defer access to page)
			if ($this->adminSessionActive && $isAdminPage)
				$allowAccess = true;

			// user is admin
			if ($userIsAdmin)
				$allowAccess = true;

			// page isn't necessarily admin page but user is logged in and has access
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

	public function renderPage() {

		echo $this->getResource();

	}

}

