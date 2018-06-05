<?php
/*
 * CoreServicesAPIPageBuilder.php
 * Initial commit: Paul Anderson, 4/12/2017
 *
 * Instantiates an informational DataResource object as the root of the Core Services REST API.
 *
 */

require_once LIBPATH.'php/toonces.php';

class CoreServicesAPIPageBuilder extends APIPageBuilder {
    
    function buildPage() {
        //  It's a CoreServicesDataResource
        $coreServicesDataResource = new CoreServicesDataResource($this->pageViewReference);
        array_push($this->resourceArray, $coreServicesDataResource);
        return $this->resourceArray;
        
    }
}
