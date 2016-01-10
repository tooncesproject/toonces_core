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

$sessionManager = new SessionManager();
$sessionManager->beginSession();

// Detect logout.
$logoutSignal = isset($_POST['logout']) ? intval($_POST['logout']) : 0;
if ($logoutSignal == 1) {
	$sessionManager->logout();
}


// POST receivers
// receive blog form submission
$blogid = isset($_POST['blogid']) ? $_POST['blogid'] : '';
$author = isset($_POST['author']) ? $_POST['author'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$body = isset($_POST['body']) ? $_POST['body'] : '';
$pageBuilderClass = isset($_POST['pageBuilderClass']) ? $_POST['pageBuilderClass'] : '';
$thumbnailImageVector = isset($_POST['thumbnailImageVector']) ? $_POST['thumbnailImageVector'] : '';

// Detect blog post data; if exists, create new blog post
if (isset($_POST['blogid'])) {
	$blogPoster = new BlogPoster($blogid, $author, $title, $body, $pageBuilderClass, $thumbnailImageVector);
}

// session stuff


$loginSuccess = 0;
$sessionActive = 0;
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['psw']) ? $_POST['psw'] : '';

if ($username != '') {
	$loginSuccess = $sessionManager->login($username, $password);
}

if ($loginSuccess == 1) {
	$sessionActive = 1;
}

if (isset($_SESSION['userId'])) {
	$sessionActive = 1;
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

// if it's a 0, you got a 404

if ($pageId == 0) {
	$pageBuilderClass = new FourOhFour();
} else {

	// get sql query
	$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_page_by_id.sql'),$pageId);

	$pageRecord = $conn->query($query);


	if ($pageRecord) {
		foreach ($pageRecord as $result) {
			$pathName = $result["pathname"];
			$styleSheet = $result["css_stylesheet"];
			$pageViewClass = $result["pageview_class"];
			$pageBuilderClass = $result["pagebuilder_class"];
			$pageTitle = $result["page_title"];
			$pageLinkText = $result["page_link_text"];
		};
	}

}
	
// instantiate the page renderer
$pageView = new $pageViewClass($pageId);

// If login attempted, pass signal to PageView object
if (isset($_POST['psw'])) {
	$pageView->loginSuccess = $loginSuccess;
}

// pass session manager if logged in

if ($sessionActive == 1) {
	$pageView->sessionManager = $sessionManager;
//	$pageView->loginSuccess = 1;
}

// set PageView class variables

$pageView->setStyleSheet($styleSheet);
//$pageView->styleSheet = $styleSheet;

$pageView->setPageTitle($pageTitle);

$pageView->setPageLinkText($pageLinkText);

$pageBuilder = new $pageBuilderClass;


$pageElements = $pageBuilder->buildPage($pageView);

foreach($pageElements as $element) {	
	$pageView->addElement($element);
}


$pageView->renderPage();




?>