<?php
/*
 * CoreServicesDataResource.php
 * Initial Commit: Paul Anderson, 4/18/2018
 *
 * A simple root resource for the Toonces Core Services API providing links to the API's endpoints.
 *
*/

require_once LIBPATH . 'php/toonces.php';

class CoreServicesDataResource extends DataResource implements iResource {

    function getAction() {
        $this->resourceData['status'] = 'I\'m a little teapot, short and stout!';
        $subResourcesAvailable = $this->getSubResources();
        if ($subResourcesAvailable) {
            $this->resourceData['status'] = 'Welcome to the Toonces Core Services API v1.0.';
        }

    }
}
