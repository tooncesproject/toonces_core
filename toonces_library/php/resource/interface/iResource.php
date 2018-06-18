<?php
/**
 * @author paulanderson
 * Initial Commit: Paul Anderson 8/15/15
 */


require_once LIBPATH . 'php/toonces.php';

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

    /**
     * @return int
     */
    public function getHttpStatus();

    public function render();
}
