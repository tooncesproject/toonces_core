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
        
        // Instantiate an Element
        $element = new HTMLResource($this->pageViewReference);
        $pageID = $this->pageViewReference->pageId;
        
        // Query the database for this page's content HTML file
        if (!isset($this->conn))
            $this->conn = $this->pageViewReference->getSQLConn();
 
        $sql = 'SELECT html_path FROM ext_html_pages WHERE page_id = :pageID';
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute(['pageID' => $pageID]);
        $result = $stmt->fetchAll();
        $htmlPath = $result[0][0];
        
        // Set Content Element's HTML to the file referenced.
        try {
            $element->html = file_get_contents($htmlPath);
        } catch (Exception $e) {
            die('Failed to get static HTML content: ' . $e->getMessage());
        }
        
        $this->contentElement = $element; 
        
    }
    
}