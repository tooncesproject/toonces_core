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
 * @param string $endpointOperatorBuilderClass
 * @return iEndpointOperatorBuilder
 */
function makeEndpointOperatorBuilder($endpointOperatorBuilderClass) {
    return new $endpointOperatorBuilderClass;
}


/*************** BEHOLD THE MAGIC OF TOONCES ***************/

// Acquire URI from request
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url,PHP_URL_PATH);

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

// Get config parameters
$parameters = parse_ini_file(LIBPATH . 'settings/toonces.ini');

// Instantiate an EndpointOperatorBuilder
$endpointOperatorBuilder = makeEndpointOperatorBuilder($parameters['endpointOperatorBuilderClass']);

// Instantiate an EndpointOperator
$endpointOperator = $endpointOperatorBuilder->makeEndpointSystem();

// Acquire client request
$request = StaticRequestFactory::getActiveRequest();

// get an Endpoint
try {
    $endpoint = $endpointOperator->readEndpointByUri($request->uri);
} catch (EndpointNotFoundException $e) {
    $endpoint = new Endpoint();
    $endpoint->resourceClassName = $parameters['resource404Class'];
}

// get a Resource
$resource = StaticResourceFactory::makeResource($endpoint);

// get Response
$response = $resource->processRequest($request);

// Render response
$response->render();
