<?php
/**
 * @author paulanderson
 * 
 * ExtHtmlResource.php
 * Initial commit: 4/25/2018
 * 
 * Acquires HTML content from a file referenced by the table ext_html_page table.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class ExtHtmlResource extends HTMLResource implements iResource {

    function getResource() {
        // Query the database for this page's content HTML file 
        $conn = $this->pageViewReference->getSQLConn();

        $sql = 'SELECT html_path, client_classs FROM ext_html_page WHERE page_id = :pageId';
        $stmt = $conn->prepare($sql);

        $stmt->execute(['pageId' => $this->pageViewReference->pageId]);
        $result = $stmt->fetchAll();
        $htmlPath = $result[0]['html_path'];
        $clientClass = $result[0]['client_class'];

        // Dynamically instantiate a client.
        $client = new $clientClass;
        
        // Get the HTML resource.
        $this->html = $client->get($htmlPath);
        /*
        // Set HTML to the file referenced.
        try {
            $this->html = file_get_contents($htmlPath);
        } catch (Exception $e) {
            echo('Failed to get static HTML content: ' . $e->getMessage());
            throw $e;
        }
        */
        
        return $this->html;
    }
}
