<?php
/**
*	index.php
*	Copyright (c) 2018 by Paul Anderson, All Rights Reserved
*
*	This script is the root script for any given application in the site.
*	It instantiates an iPageView-compliant object which provides the base rendering for a resource
*
*/

include_once 'config.php';
require __DIR__ . '/vendor/autoload.php';

/*************** BEHOLD THE MAGIC OF TOONCES ***************/

// Acquire URI from request
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url,PHP_URL_PATH);

// trim last slash from path
$path = substr($path,1,strlen($path)-1);

// Get config parameters
$parameters = parse_ini_file(LIBPATH . 'settings/toonces.ini');

// Instantiate an EndpointOperator
$endpointOperator = StaticEndpointOperatorFactory::makeEndpointOperator();

// Acquire client request
$request = StaticRequestFactory::getActiveRequest();

// get an Endpoint
try {
    $endpoint = $endpointOperator->readEndpointByUri($request->uri);
} catch (EndpointNotFoundException $e) {
    $endpoint = new Endpoint(
        0,
        '',
        $parameters['resource404Class']
    );

}

// get a Resource
$resource = StaticResourceFactory::makeResource($endpoint);

// get Response
$response = $resource->processRequest($request);

// Render response
$response->render();
