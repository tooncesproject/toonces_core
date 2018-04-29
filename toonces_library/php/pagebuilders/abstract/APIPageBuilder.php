<?php
/*
 * APIPageBuilder.php
 * Initial commit: Paul Anderson, 1/24/2018
 * Base abstract class extending PageBuilder to form the root resource of a REST API "page."
 *
 */

require_once LIBPATH.'php/toonces.php';

abstract class APIPageBuilder
{

    var $resourceArray = array();
    var $pageViewReference;

    function __construct($pageview) {
        $this->pageViewReference = $pageview;
    }

    function buildPage() {

        return $this->resourceArray;
    }
}
