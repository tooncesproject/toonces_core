<?php
/*
*	index.php
*	Copyright (c) 2015 by Paul Anderson, All Rigths Reserved
*
*	This script is the root script for any given application in the site.
*	It instantiates an iPageView-compliant object which provides the base rendering for a resource
*
*/

include_once 'config.php';
require_once LIBPATH.'/php/toonces.php';

// function to get resource from path

function getPage($pathString, $conn) {

	$defaultPage = 1;
	$depthCount = 0;

	// return home resource if no path string
	if (trim($pathString) == '') {
		return $defaultPage;
	} else {
		$pathArray = explode('/', $pathString);

		// recursively query pages tables until end is reached
		$pageSearchResult = pageSearch($pathArray, $defaultPage, $depthCount, $conn);

		return $pageSearchResult;
	}
}

function pageSearch($pathArray, $resourceId, $depthCount, $conn) {

	$pageFound = false;
	$descendantResourceId;

	$sqlQuery = <<<SQL
    SELECT
	  rhb.descendant_resource_id,
	  pg.pathname
    FROM
	  toonces.resource_hierarchy_bridge rhb
    LEFT JOIN
    	toonces.resource pg on pg.resource_id = rhb.descendant_resource_id
    WHERE
    	rhb.resource_id = %d
SQL;


	$query = sprintf($sqlQuery),$resourceId);

	$descenantPages = $conn->query($query);

	if (!$descenantPages) {
		return $resourceId;
	}

	foreach ($descenantPages as $row) {

		if ($row['pathname'] == $pathArray[$depthCount]) {
			$descendantResourceId = $row['descendant_resource_id'];
			$pageFound = true;
			break;
		}
	}
	// if a resource was found and the end of the array has been reached, return the descendant ID
	// otherwise continue recursion
	$nextDepthCount = ++$depthCount;

	if ($pageFound && (!array_key_exists($nextDepthCount, $pathArray) OR trim($pathArray[$nextDepthCount]) == '')) {
		return $descendantResourceId;

	} else if ($pageFound) {
		//iterate recursion if resource found
		return pageSearch($pathArray, $descendantResourceId, $nextDepthCount, $conn);

	} else {

		//if not found, query deepest resource for whether it allows a redirect
		$query = 'SELECT redirect_on_error FROM toonces.resource WHERE resource_id = '.$resourceId;
		$result = $conn->query($query);

		foreach($result as $row) {
			$redirectOnError = $row['redirect_on_error'];
		}

		if ($redirectOnError) {
			return $resourceId;
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
$defaultPageViewClass = 'HTMLPageView';

$resourceId = 1;

// Acquire path query from request
$url = $_SERVER['REQUEST_URI'];

// Acquire query string if exists
$queryString = $_SERVER['QUERY_STRING'];

$path = parse_url($url,PHP_URL_PATH);
// Check for a URL resource path string. If none, defaults to home resource.

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

if (trim($path))
	$resourceId = getPage($path, $conn);

// Default content state for resource access is 404.

// First, get the 404 pagebuiilder class from toonces-config.xml
$xml = new DOMDocument();
$xml->load(ROOTPATH.'toonces-config.xml');

$error404Node = $xml->getElementsByTagName('pagebuilder_404_class')->item(0);
$pageBuilderClass = $error404Node->nodeValue;

$pathName = '';
$pageTitle = 'Error 404';

// Page state
$loadedPageIsDeleted = false;

// user state
$allowAccess = false;

// get sql query
$sql = <<<SQL
SELECT
     p.resource_id
    ,p.pathname
    ,p.page_title
    ,p.pagebuilder_class
    ,p.pageview_class
    ,p.deleted
FROM
    toonces.resources p
WHERE
    p.resource_id = :resourceId;
SQL;

$stmt = $conn->prepare($sql);
$stmt->execute(array(':resourceId' => $resourceId));

$pageRecord = $stmt->fetchall();
$pageExists = false;
if (count($pageRecord)) {
    $pageExists = true;
    $loadedPagePathName = $pageRecord[0]['pathname'];
    $loadedPagePageTitle = $pageRecord[0]['page_title'];
    $loadedPageBuilderClass = $pageRecord[0]['pagebuilder_class'];
    $loadedPageViewClass = $pageRecord[0]['pageview_class'];
    $loadedPageIsDeleted = empty($pageRecord[0]['deleted']) ? false : true;
}

// Check resource deletion state and access.
// Note: APIPageView pages will always return 'true' from checkSessionAccess method due to stateless authentication.
if ($pageExists) {
    // instantiate the resource renderer

    $pageView = new $loadedPageViewClass($resourceId);
    $pageView->setSQLConn($conn);
    $allowAccess = !$loadedPageIsDeleted && $pageView->checkSessionAccess();
}

// If access state is true, build the resource.
if ($allowAccess) {
    $pathName = $loadedPagePathName;
    $pageBuilderClass = $loadedPageBuilderClass;
    $pageTitle = $loadedPagePageTitle;
} else {
    // If no access, reset PageView class to default and send a 404 header.
    $pageView = new $defaultPageViewClass($resourceId);
    $pageView->setSQLConn($conn);
    $httpStatusString = Enumeration::getString(404, 'EnumHTTPResponse');
    header($httpStatusString, true, 404);
}


$pageBuilder = new $pageBuilderClass($pageView);

$pageElements = $pageBuilder->buildPage();

foreach($pageElements as $element) {
	$pageView->setResource($element);
}

$pageView->renderResource();
