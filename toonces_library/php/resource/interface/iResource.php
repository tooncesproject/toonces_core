<?php
/*
 * iResource Interface
 * Paul Anderson 8/15/15
 *
 */

interface iResource
{
    /**
     * @param int $paramResourceId
     * @return void
     */
    public function setResourceId($paramResourceId);

    /**
     * @return int
     */
    public function getResourceId();

    public function getResource();
    public function render();
}
