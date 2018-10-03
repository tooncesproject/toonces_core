<?php
/**
 * @author paulanderson
 * Date: 10/1/18
 * Time: 9:40 PM
 */

abstract class Responder implements iResponder {

    var $resource;

    /**
     * Responder constructor.
     * @param Resource $paramResource
     */
    public function __construct($paramResource)
    {
        $this->resource = $paramResource;
    }

}
