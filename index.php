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
include 'toonces_library/PageView.php';
include_once ROOTPATH.'/interfaces/iView.php';
include_once ROOTPATH.'/custom/CentagonPageBuilder.php';
include_once ROOTPATH.'/static_classes/SQLConn.php';
include_once ROOTPATH.'/utility/UniversalConnect.php';

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
		return pageSearch($pathArray, $defaultPage, $depthCount, $conn);
	}
}

function pageSearch($pathArray, $pageid, $depthCount, $conn) {
	
	echo implode('x',$pathArray).PHP_EOL.'<BR>';
	echo $depthCount.PHP_EOL.'<br>';
	$pageFound = false;
	$descendantPageId;
		
	$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_child_page_ids.sql'),$pageid);
	echo $query.'<br>';
	
	$descenantPages = $conn->query($query);
	
	if (!$descenantPages)
		return $pageid;
	
	foreach ($descenantPages as $row) {
			echo $row['pathname'].PHP_EOL;
			echo $pathArray[$depthCount].PHP_EOL;
		if ($row['pathname'] == $pathArray[$depthCount]) {
			$descendantPageId = $row['descendant_page_id'];
			$pageFound = true;
			break;
		}
	}
	// if a page was found and the end of the array has been reached, return the descendant ID
	// otherwise continue recursion
	if ($pageFound /*&& !$pathArray[$depthCount++]*/) 
		return $descendantPageId;
	else if ($pageFound)
		pageSearch($pathArray, $descendantPageId, $depthCount++ , $conn);
	
	//if not found, query deepest page for whether it allows a redirect
	$query = 'SELECT redirect_on_error FROM toonces.pages WHERE page_id = '.$pageid;
	$result = $conn->query($query);
		
	foreach($result as $row) {
		$redirectOnError = $row['redirect_on_error'];
	}
	
	if ($redirectOnError) 
		return $pageid;
	else
		return 0;
}



// set default properties for view renderer setter methods
$pageTitle = 'Hello World';
$styleSheet = '/toonces_library/css/centagon_v1.css';
$htmlHeader = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$htmlFooter = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$pageViewClass = 'PageView';
$pageBuilderClass = 'CentagonPageBuilder';

$pageId = 1;

// Acquire path query from request
$path = $_SERVER['REQUEST_URI']; 

$path = substr($path,1,strlen($path)-1);


// establish SQL connection and query for page
$conn = UniversalConnect::doConnect();

// Check for a URL query string. If none, defaults to home page.
/*
if (trim($path)) {
	$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_page.sql'),$path,1);
} else {
	$query = file_get_contents(ROOTPATH.'/sql/retrieve_home_page.sql');
}
*/
if (trim($path))
	$pageId = getPage($path, $conn);	

// get sql query

$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_page_by_id.sql'),$pageId);

$pageRecord = $conn->query($query);


if ($pageRecord) {
	foreach ($pageRecord as $result) {
		$pathName = $result["pathname"];
		$styleSheet = '/toonces_library/css/'.$result["css_stylesheet"];
		$pageViewClass = $result["pageview_class"];
		$pageBuilderClass = $result["pagebuilder_class"];
		$pageTitle = $result["page_title"];
	};
}

// instantiate the page renderer
$pageView = new $pageViewClass;

// iView interface setter methods
$pageView->setHtmlHeader($htmlHeader);
$pageView->setHtmlFooter($htmlFooter);

// set PageView class variables
$pageView->pageTitle = $pageTitle;
$pageView->styleSheet = $styleSheet;
$pageView->metaTag = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';

$pageBuilder = new $pageBuilderClass;
$pageElements = $pageBuilder->buildPage();

foreach($pageElements as $element) {	
	$pageView->addElement($element);
}



//$pageView->addElement($herro);

$pageView->renderPage();




?>