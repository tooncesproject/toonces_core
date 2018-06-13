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

/**
 * @return iResourceFactory
 */
function makeResourceFactory() {
    $xml = new DOMDocument();
    $xml->load(ROOTPATH.'toonces-config.xml');
    $resourceFactoryNode = $xml->getElementsByTagName('resource_factory_class')->item(0);
    $resourceFactoryClass = $resourceFactoryNode->nodeValue;

    return new $resourceFactoryClass;

}

/*************** BEHOLD THE MAGIC OF TOONCES ***************/

// Acquire URI from request
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url,PHP_URL_PATH);

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

// Instantiate ResourceFactory
$resourceFactory = makeResourceFactory();

$resource = $resourceFactory->makeResource($path);

$resource->render();
