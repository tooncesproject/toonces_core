<?php

require_once LIBPATH.'php/toonces.php';

class PageView extends ViewElement implements iResourceView, iHTMLView
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
	var $urlPath;
	var $pageIsPublished;
	var $conn;

	public function __construct($pageViewPageId) {
		$this->pageId = $pageViewPageId;
		parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
	}


	public function addElement ($element) {

		array_push($this->pageElements,$element);
		$this->elementsCount++;

	}

	// execution methods
	public function checkSessionAccess() {
	    
	    $userID = NULL;
	    $userIsAdmin = false;
	    $isAdminPage = false;
	    $pageIsPublished = false;
	    $userHasPageAccess = false;
	    $userCanEdit = false;

	    // Instantiate session manager and check status.
	    $sessionManager = new SessionManager($conn);
	    $sessionManager->checkSession();
	    $adminSessionActive = $sessionManager->adminSessionActive;

	    if ($adminSessionActive == 1) {
	        $userID = $sessionManager->userId;
	        $userIsAdmin = $sessionManager->userIsAdmin;
	    }

	    if ($userID) {
	    // Query the database for publication and user access state
	       $sql = <<<SQL
            SELECT
                ,p.published
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
	    
	       $stmt = $this->conn->prepare($sql);
	       $stmt->execute(array(':userID' =>userID, ':pageID' => $this->pageId));
	       $result = $stmt->fetchOne();
	       
	       $pageIsPublished = $result['published'];
	       $pageTypeId = $result['pagetype_id'];
	       $isAdminPage = ($result['pagetype_id'] == 1) ? true : false;
	       $userHasPageAccess = $result['user_has_access'];
	       // Admin user can always edit
	       $userCanEdit = ($userIsAdmin) ? true : $result['can_edit']; 

	    }

	    // Is the page unpublished?
	    if (!$pageIsPublished) {
	        // Allow access to page if:
	        // user is logged in and page is admin page (defer access to page)
	        if ($adminSessionActive && $isAdminPage) 
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

