<?php
/*
 * ExtHTMLPageBuilder
 * Initial Commit: Paul Anderson, 10/20/2017
 * 
 * Subclass of StandardPageBuilder; References SQL for a vector to a static HTML file for the ContentElement.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class ExtHTMLPageBuilder extends StandardPageBuilder {

    function createContentElement() {
        
        // Pretty simple here - It's just an extHtmlResource object.
        $this->contentElement = new ExtHtmlResource($this->pageViewReference->pageId); 
        
    }
    
}