<?php
/**
 * @author paulanderson
 * initial commit: 6/12/18
 * Time: 9:10 PM
 *
 * Interface defining requirements for endpointoperator objects.
 *
 */


interface iEndpointOperator
{


    /**
     * @param integer $parentEndpointId
     * @param string $pathName
     * @param string $resourceClassName
     * @return Endpoint
     */
    public function createEndpoint($parentEndpointId, $pathName, $resourceClassName);

    /**
     * @param string $endpointUri;
     * @throws EndpointNotFoundException
     * @return Endpoint
     */
    public function readEndpointByUri($endpointUri);

    /**
     * @param integer $endpointId
     * @param boolean $recursive
     * @return Endpoint
     */
    public function readEndpointById($endpointId, $recursive = false);

    /**
     * @param Endpoint $endpoint
     * @return Endpoint
     */
    public function updateEndpoint($endpoint);

    /**
     * @param integer $endpointId
     * @return Endpoint
     */
    public function deleteEndpoint($endpointId);

}
