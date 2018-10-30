<?php
/**
 * @author paulanderson
 * Date: 10/29/18
 * Time: 10:27 PM
 */

class StaticResourceFactory
{
    /**
     * @param Endpoint $endpoint
     * @return Resource
     */
    public static function makeResource($endpoint)
    {
        $resource = new $endpoint->resourceClassName;
        $resource->endpointId = $endpoint->endpointId;
        return $resource;

    }
}
