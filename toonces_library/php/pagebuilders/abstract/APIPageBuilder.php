<?php
/*
 * APIPageBuilder.php
 * Initial commit: Paul Anderson, 1/24/2018
 * Base abstract class extending PageBuilder to form the root resource of a REST API
 * 
 */

require_once LIBPATH.'php/toonces.php';

abstract class APIPageBuilder extends PageBuilder
{
    function buildPage() {
        // 
    }
}