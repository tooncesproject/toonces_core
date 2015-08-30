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


// set default properties for view renderer setter methods
$pageTitle = 'Hello World';
$styleSheet = '/toonces_library/css/centagon_v1.css';
$htmlHeader = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$htmlFooter = file_get_contents(ROOTPATH.'/static_data/generic_html_header.html');
$pageViewClass = 'PageView';
$pageBuilderClass = 'CentagonPageBuilder';

// Acquire path query from request
$path = $_SERVER['REQUEST_URI']; 
$path = substr($path,1,strlen($path)-1);

// establish SQL connection and query for page
$conn = UniversalConnect::doConnect();
$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_page.sql'),$path,1);
echo $query;

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

// instantiate the view singleton
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