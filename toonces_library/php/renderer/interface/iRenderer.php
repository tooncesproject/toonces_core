<?php
/*
 * iRenderer.php
 * Initial commit: Paul Anderson, 1/24/2016
 * Project: API/Core/REST refactor
 * Provides an interface for duck-typing "Renderer" classes assigned to a "Page"/URI as
 * delegate to index.php
 */

require_once LIBPATH . 'php/toonces.php';

interface iRenderer
{

    /**
     * @param Resource $resource
     */
    public function render($resource);

}