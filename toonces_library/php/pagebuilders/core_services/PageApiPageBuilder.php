<?php
/**
 * @author paulanderson
 * PageApiPageBuilder.php
 * Initial commit: 5/4/2018
 * 
 * Pagebuilder creating an endpoint managing pages as part of
 * the Core Services API
 * 
 */

require_once LIBPATH.'php/toonces.php';

class PageApiPageBuilder extends APIPageBuilder {
    
    function buildPage() {
        // It's a PageDataResource
        $pdr = new PageDataResource($this->pageViewReference);
        array_push($this->resourceArray, $pdr);
        return $this->resourceArray;
        
    }
    
}