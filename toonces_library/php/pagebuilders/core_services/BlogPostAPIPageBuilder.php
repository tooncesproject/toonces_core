<?php
/*
 * BlogPostAPIPageBuilder.php
 * Initial commit: Paul Anderson, 4/12/2017
 * 
 * Generates a root resource for managing Toonces blogs.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogPostAPIPageBuilder extends APIPageBuilder {
    
    var $apiDelegate;
    var $conn;

        
    function buildPage() {
        //  It's a BlogPostDataResource
        $blogDataResource = new BlogPostDataResource($this->pageViewReference);
        array_push($this->resourceArray, $blogDataResource);
        return $this->resourceArray;
            
    }
}
