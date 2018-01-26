<?php
/*
*	index.php
*	Copyright (c) 2015 by Paul Anderson, All Rigths Reserved
*
*	This script is the root script for any given application in the site.
*	It instantiates an iResourceView-compliant object which provides the base rendering for a page
*
*/

include_once 'config.php';
require_once LIBPATH.'/php/toonces.php';

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

	$query = sprintf(file_get_contents(LIBPATH.'/sql/query/retrieve_child_page_ids.sql'),$pageid);

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

// ******************** Begin procedural code ******************** 

// establish SQL connection
$conn = UniversalConnect::doConnect();

// set default properties for view renderer setter methods
$pageTitle = 'Toonces Page';
$pageViewClass = 'PageView';

$pageId = 1;

// Acquire path query from request
$url = $_SERVER['REQUEST_URI'];

// Acquire query string if exists
$queryString = $_SERVER['QUERY_STRING'];

$path = parse_url($url,PHP_URL_PATH);
// Check for a URL page path string. If none, defaults to home page.

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

if (trim($path))
	$pageId = getPage($path, $conn);

// Default content state for page access is 404.

// First, get the 404 pagebuiilder class from toonces-config.xml
$xml = new DOMDocument();
$xml->load(ROOTPATH.'toonces-config.xml');

$error404Node = $xml->getElementsByTagName('pagebuilder_404_class')->item(0);
$pageBuilderClass = $error404Node->nodeValue;

$pathName = '';
$pageTitle = 'Error 404';
$pageLinkText = '';

// Page state
$pageTypeId = 0;
$pageIsDeleted = false;

// user state
$allowAccess = 0;

// get sql query
$sql = <<<SQL
SELECT
     p.page_id
    ,p.pathname
    ,p.page_title
    ,p.page_link_text
    ,p.pagebuilder_class
    ,p.pageview_class
    ,p.pagetype_id
    ,p.deleted
FROM
    toonces.pages p
WHERE
    p.page_id = %d;
SQL;

$query = sprintf($sql,$pageId);

$pageRecord = $conn->query($query);

if ($pageRecord->rowCount() > 0) {
	foreach ($pageRecord as $result) {
		$pagePathName = $result['pathname'];
		$pagePageTitle = $result['page_title'];
		$pagePageLinkText = $result['page_link_text'];
		$pagePageBuilderClass = $result['pagebuilder_class'];
		$pagePageViewClass = $result['pageview_class'];
		$pageTypeId = $result['pagetype_id'];
		$pageIsDeleted = empty($result['deleted']) ? false : true; 
	};



}

// If access state is true, build the page.
if ($allowAccess) {
	$pathName = $pagePathName;
	$pageViewClass = $pagePageViewClass;
	$pageBuilderClass = $pagePageBuilderClass;
	$pageTitle = $pagePageTitle;
	$pageLinkText = $pagePageLinkText;
}

// instantiate the page renderer
$pageView = new $pageViewClass($pageId);
$pageView->setPageURI($path);
$pageView->setSQLConn($conn);

// Check page deletion state and access.
// Note: APIView pages will always return 'true' from checkSessionAccess method due to stateless authentication.
$allowAccess = !$pageIsDeleted && $pageView->checkSessionAccess();

// set PageView class variables
$pageView->setPageTitle($pageTitle);
$pageView->setPageLInkText($pageLinkText);
$pageView->setPageTypeID($pageTypeId);

$pageBuilder = new $pageBuilderClass($pageView);

$pageElements = $pageBuilder->buildPage();

foreach($pageElements as $element) {
	$pageView->addElement($element);
}

$pageView->renderPage();
