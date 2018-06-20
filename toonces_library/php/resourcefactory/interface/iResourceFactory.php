<?php
/**
 * iResourceFactory.php
 * @author paulanderson
 * initial commit: 6/12/18
 * Time: 9:10 PM
 *
 * Interface defining requirements for resourcefactory objects.
 *
 */

require_once LIBPATH . '/php/toonces.php';

interface iResourceFactory {
    /**
     * @param $resourceUri
     * @return iResource
     */
    public function makeResource($resourceUri);
}
