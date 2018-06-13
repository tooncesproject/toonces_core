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

        $sql = 'SELECT html_path, client_class FROM ext_html_page WHERE resource_id = :resourceId';
        $stmt = $conn->prepare($sql);

        $stmt->execute(['resourceId' => $this->pageViewReference->resourceId]);
        $result = $stmt->fetchAll();
        $htmlPath = $result[0]['html_path'];
        $clientClass = $result[0]['client_class'];

        // Dynamically instantiate a client.
        $client = new $clientClass;

        // Get the HTML resource.
        $this->html = $client->get($htmlPath);

        return $this->html;
    }
}
