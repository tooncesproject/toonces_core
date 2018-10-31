<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 1:57 PM
 */

class Endpoint {

    /**
     * Endpoint constructor.
     * @param integer $endpointId
     * @param string $pathname
     * @param string $resourceClassName
     * @param array|null $children
     */
    public function __construct(
        $endpointId,
        $pathname,
        $resourceClassName,
        $children = null
    )
    {
        $this->endpointId = $endpointId;
        $this->pathname = $pathname;
        $this->resourceClassName = $resourceClassName;
        $this->children = $children;
    }

    /** @var integer */
    var $endpointId;

    /** @var string */
    var $pathname;

    /** @var string */
    var $resourceClassName;

    /** @var array */
    var $children;

}
