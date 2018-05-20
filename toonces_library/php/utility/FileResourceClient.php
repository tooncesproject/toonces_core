<?php
/**
 * @author paulanderson
 * FileResourceClient.php
 * Initial commit: Paul Anderson, 5/5/2018
 *
 * An iResourceClient compliant class providing authentication for a
 * local file resource.
 *
 * */

include_once LIBPATH.'php/toonces.php';

class FileResourceClient implements iResourceClient {

    var $conn;
    var $resourceStatus;
    var $pageViewReference;

    function getHttpStatus() {
        return $this->resourceStatus;
    }

    function getResourcePath() {
        // By default, FRC uses the html_resource_path var specified in toonces-config.xml, just like
        // the DocumentEndpointPageBuilder.
        $xml = new DOMDocument();
        $xml->load(ROOTPATH.'toonces-config.xml');

        $pathNode = $xml->getElementsByTagName('html_resource_path')->item(0);
        $path = $pathNode->nodeValue;
        return $path;
    }

    function get($url) {
        $fileResource = new FileResource(null);
        $fileResource->resourcePath = $this->getResourcePath();
        $fileResource->requireAuthentication = false;
        $fileResource->requestPath = $url;
        $fileVector = $fileResource->getAction();

        return file_get_contents($fileVector);
    }


    function put($url, $data) {

        if (!isset($this->conn))
            $this->conn = $this->pageViewReference-getSQLConn();

        // Instantiate fileResource and set dependencies.
        $fileResource = new FileResource(null);
        $fileResource->conn = $this->conn;
        // Acquire the page ID attributed to the URL so the fileResource
        // object can authenticate user access to the page.
        $path = parse_url($url, PHP_URL_PATH);
        $pathArray = explode('/', $path);
        $pageId = SearchPathString::grabPageId($pathArray, 1, 0, $this->conn);
        $fileResource->pageId = $pageId;
        $fileResource->requestPath = $url;
        $fileResource->resourceData = $data;

        $response = $fileResource->putAction();
        $this->resourceStatus = $fileResource->httpStatus;
        return $response;

    }


    function delete($url) {
        if (!isset($this->conn))
            $this->conn = $this->pageViewReference-getSQLConn();

        // Instantiate fileResource and set dependencies.
        $fileResource = new FileResource(null);
        $fileResource->conn = $this->conn;
        // Acquire the page ID attributed to the URL so the fileResource
        // object can authenticate user access to the page.
        $path = parse_url($url, PHP_URL_PATH);
        $pathArray = explode('/', $path);
        $pageId = SearchPathString::grabPageId($pathArray, 1, 0, $this->conn);
        $fileResource->pageId = $pageId;
        $fileResource->requestPath = $url;

        $response = $fileResource->deleteAction();
        $this->resourceStatus = $fileResource->httpStatus;
        return $response;
    }

    function __construct($pageView) {
        $this->pageViewReference = $pageView;
    }
}
