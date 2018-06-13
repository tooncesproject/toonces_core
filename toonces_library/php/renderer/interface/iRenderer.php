<?php
/*
 * iRenderer.php
 * Initial commit: Paul Anderson, 1/24/2016
 * Project: API/Core/REST refactor
 * Provides an interface for duck-typing "Renderer" classes assigned to a "Page"/URI as
 * delegate to index.php
 */


interface iRenderer
{
    /**
     * @param iResource $paramResource
     */
    public function renderResource($paramResource);

}