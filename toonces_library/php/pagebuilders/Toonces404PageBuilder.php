<?php
/*
 * Toonces404PageBuilder.php
 * Initial Commit: Paul Anderson, 1/8/2018
 *
 * Subclass of StandardPageBuilder; provides a standardized 404 error page
 *
 */

require_once LIBPATH.'php/toonces.php';

class Toonces404PageBuilder extends StandardPageBuilder {
    
    function createContentElement() {

        // Instantiate an Element
        $element = new Element($this->pageViewReference);
        $pageID = $this->pageViewReference->pageId;
        
        $html= <<<HTML
		<div class="copy_block">
				<h1>404</h1>
				<h2>Sorry, you have reached a page that doesn't exist.</h2>
				<p><a href="/">Click here to visit the home page.</a></p>
				
		</div>
HTML;
        
        $element->html = $html;
            
        $this->contentElement = $element;
            
    }
    
}