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
 * @param string $tagName
 * @return string
 */
function getConfigNodeValue($tagName) {
    $xml = new DOMDocument();
    $xml->load(ROOTPATH.'settings/toonces-config.xml');
    $resourceFactoryNode = $xml->getElementsByTagName($tagName)->item(0);
    return $resourceFactoryNode->nodeValue;
}

/**
 * @return iEndpointOperator
 */
function makeResourceFactory() {

    $resourceFactoryClass = getConfigNodeValue('resource_factory_class');
    return new $resourceFactoryClass;

}

/**
 * @return iEndpointOperatorBuilder
 */
function makeEndpointSystemFactory() {
    $endpointSystemFactoryClass = getConfigNodeValue('endpoint_system_factory_class');
    return new $endpointSystemFactoryClass;
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

// Acquire client request
$request = StaticRequestFactory::getActiveRequest();

// get Response
$response = $resource->processRequest($request);

// Render response
$response->render();
