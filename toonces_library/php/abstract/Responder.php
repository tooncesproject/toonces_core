<?php
/**
 * @author paulanderson
 * Date: 10/1/18
 * Time: 9:40 PM
 */

abstract class Responder {

    var $resource;

    /**
     * Responder constructor.
     * @param Resource $paramResource
     */
    public function __construct($paramResource)
    {
        $this->resource = $paramResource;
    }

    /**
     * @return Response
     */
    public function respond($paramRequest)
    {
        throw new BadMethodCallException('Responder subclasses must implement respond($paramRequest');
    }

}
