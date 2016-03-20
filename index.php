<?php
/*
*	index.php
*	Copyright (c) 2015 by Paul Anderson, All Rigths Reserved
*
*	This script is the root script for any given application in the site.
*	It instantiates a PageView object which provides the base rendering for a page
*
*
*
*/

include 'toonces_library/config.php';
require_once ROOTPATH.'/toonces.php';

// global variables
$gBlogDefaultPagebuilder = 'CentagonBlogPostSingle';

$sessionManager = new SessionManager();

$sessionManager->checkSession();


// Detect blog post data; if exists, create new blog post
if (isset($_POST['blogid'])) {
	$blogPoster = new BlogPoster($blogid, $author, $title, $body, $pageBuilderClass, $thumbnailImageVector);
}

// session stuff

// $loginSuccess = 0;
$adminSessionActive = 0;
$userId = 0;
$userIsAdmin = 0;

$adminSessionActive = $sessionManager->adminSessionActive;

if ($adminSessionActive == 1) {
	$userId = $sessionManager->userId;
	$userIsAdmin = $sessionManager->userIsAdmin;
}

// function to get page from path

function getPage($pathString, $conn) {

	$defaultPage = 1;
	$depthCount = 0;
	$pathArray = array();

	// return home page if no path string
	if (trim($pathString) == '') {
		return $defaultPage;
	} else {
		$pathArray = explode('/', $pathString);

		// recursively query pages tables until end is reached
		$pageSearchResult = pageSearch($pathArray, $defaultPage, $depthCount, $conn);

		return $pageSearchResult;
	}
}

function pageSearch($pathArray, $pageid, $depthCount, $conn) {

	$pageFound = false;
	$descendantPageId;

	$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_child_page_ids.sql'),$pageid);


	$descenantPages = $conn->query($query);

	if (!$descenantPages) {
		return $pageid;
	}

	foreach ($descenantPages as $row) {

		if ($row['pathname'] == $pathArray[$depthCount]) {
			$descendantPageId = $row['descendant_page_id'];
			$pageFound = true;
			break;
		}
	}
	// if a page was found and the end of the array has been reached, return the descendant ID
	// otherwise continue recursion
	$nextDepthCount = ++$depthCount;


	if ($pageFound && (!array_key_exists($nextDepthCount, $pathArray) OR trim($pathArray[$nextDepthCount]) == '')) {
		return $descendantPageId;

	} else if ($pageFound) {
		//iterate recursion if page found
		return pageSearch($pathArray, $descendantPageId, $nextDepthCount, $conn);

	} else {

		//if not found, query deepest page for whether it allows a redirect
		$query = 'SELECT redirect_on_error FROM toonces.pages WHERE page_id = '.$pageid;
		$result = $conn->query($query);

		foreach($result as $row) {
			$redirectOnError = $row['redirect_on_error'];
		}

		if ($redirectOnError) {
			return $pageid;
		}
		else {
			return 0;
		}
	}

}



// set default properties for view renderer setter methods
$pageTitle = 'eff';
$styleSheet = '/toonces_library/css/centagon_v1.css';
$htmlHeader = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$htmlFooter = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$pageViewClass = 'PageView';
$pageBuilderClass = 'CentagonPageBuilder';

$pageId = 1;

// Acquire path query from request
//$path = $_SERVER['REQUEST_URI'];
$url = $_SERVER['REQUEST_URI'];

// establish SQL connection and query for page
$conn = UniversalConnect::doConnect();

// Acquire query string if exists
$queryString = $_SERVER['QUERY_STRING'];

$path = parse_url($url,PHP_URL_PATH);
// Check for a URL page path string. If none, defaults to home page.

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

if (trim($path))
	$pageId = getPage($path, $conn);

// Default content state for page access is 404.
$pathName = '';
$styleSheet = ''; // Have a default stylesheet?
$pageViewClass = 'PageView';
$pageBuilderClass = 'FourOhFour';
$pageTitle = '';
$pageLinkText = '';

// Page state
$published = 0;
$isAdminPage = 0;
$pageTypeId = 0;

// user state
$allowAccess = 0;
$userCanEdit = 0;
$userHasPageAccess = 0;

// get sql query
$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_page_by_id.sql'),$userId,$pageId);

$pageRecord = $conn->query($query);

if ($pageRecord) {
	foreach ($pageRecord as $result) {
		$pagePathName = $result['pathname'];
		$pageStyleSheet = $result['css_stylesheet'];
		$pagePageViewClass = $result['pageview_class'];
		$pagePageBuilderClass = $result['pagebuilder_class'];
		$pagePageTitle = $result['page_title'];
		$pagePageLinkText = $result['page_link_text'];
		$published = $result['published'];
		$isAdminPage = ($result['pagetype_id'] == 1) ? 1 : 0;
		$pageTypeId = $result['pagetype_id'];
		$userHasPageAccess = $result['user_has_access'];
		$userCanEdit = $result['can_edit'];
	};

	// Manage access.
	// Is the page unpublished?
	if ($published == 0) {
		// Allow access to page if:
		// user is logged in and page is admin page (defer access to page)
		if ($adminSessionActive == 1 and  $isAdminPage == 1) {
			$allowAccess = 1;
		}
		// user is admin
		if ($sessionManager->userIsAdmin == 1) {
			$allowAccess = 1;

		}
		// page isn't necessarily admin page but user is logged in and has access
		if ($userHasPageAccess == 1) {
			$allowAccess = 1;
		}

	} else {
		// If published, page is public.
		$allowAccess = 1;
	}

	// If the user is an admin/root user, set userCanEdit to true
	if ($sessionManager->userIsAdmin == true)
		$userCanEdit = true;
}

// If access state is true, build the page.
if ($allowAccess == 1) {
	$pathName = $pagePathName;
	$styleSheet = $pageStyleSheet;
	$pageViewClass = $pagePageViewClass;
	$pageBuilderClass = $pagePageBuilderClass;
	$pageTitle = $pagePageTitle;
	$pageLinkText = $pagePageLinkText;
}

// instantiate the page renderer
$pageView = new $pageViewClass($pageId);
$pageView->userCanEdit = $userCanEdit;
$pageView->urlPath = $path;
$pageView->pageIsPublished = $published;

// If it's an admin page, pass the user's page access state to the pageView
if ($adminSessionActive == 1 and $isAdminPage == 1) {
	$pageView->userCanAccessAdminPage = $userHasPageAccess;
}

// pass session manager to pageView
$pageView->sessionManager = $sessionManager;

// set PageView class variables

$pageView->setStyleSheet($styleSheet);
//$pageView->styleSheet = $styleSheet;

$pageView->setPageTitle($pageTitle);

$pageView->setPageLinkText($pageLinkText);

$pageView->pageTypeId = $pageTypeId;

$pageBuilder = new $pageBuilderClass($pageView);

$pageElements = $pageBuilder->buildPage();

foreach($pageElements as $element) {
	$pageView->addElement($element);
}


$pageView->renderPage();




?>