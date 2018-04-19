<?php
/*
 * BlogsAPIPageBuilder.php
 * Initial commit: Paul Anderson, 3/9/2017
 * 
 * Generates a root resource for managing Toonces blogs.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogsAPIPageBuilder extends APIPageBuilder {
        
    function buildPage() {
        //  It's a BlogDataResource
        $blogDataResource = new BlogDataResource($this->pageViewReference);
        array_push($this->resourceArray, $blogDataResource);
        return $this->resourceArray;
            
    }
}
