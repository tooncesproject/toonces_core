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

    /**
     * @var PDO
     */
    var $conn;
    var $resourceStatus;
    var $pageViewReference;

    function connectSql() {
        if (!isset($this->conn))
            $this->conn = UniversalConnect::doConnect();
    }


    /**
     * @return int
     */
    function getHttpStatus() {
        return $this->resourceStatus;
    }


    /**
     * @return string
     */
    function getResourcePath() {
        // By default, FRC uses the html_resource_path var specified in toonces-config.xml, just like
        // the DocumentEndpointPageBuilder.
        $xml = new DOMDocument();
        $xml->load(ROOTPATH.'toonces-config.xml');

        $pathNode = $xml->getElementsByTagName('html_resource_path')->item(0);
        $path = $pathNode->nodeValue;
        return $path;
    }

    /**
     * @param string $url
     * @param null|string $username
     * @param null|string $password
     * @param array $paramHeaders
     * @return string
     * @throws Exception
     */
    function get($url, $username = null, $password = null, $paramHeaders = array()) {
        $fileResource = new FileResource();
        $fileResource->resourcePath = $this->getResourcePath();
        $fileResource->requireAuthentication = false;
        $fileResource->requestPath = $url;
        $fileVector = $fileResource->getAction();

        return file_get_contents($fileVector);
    }


    /**
     * @param string $url
     * @param $data
     * @param null|string $username
     * @param null|string $password
     * @param array $paramHeaders
     */
    function put($url, $data, $username = null, $password = null, $paramHeaders = array()) {

        $this->connectSql();

        // Instantiate fileResource and set dependencies.
        $fileResource = new FileResource(null);
        $fileResource->conn = $this->conn;
        // Acquire the page ID attributed to the URL so the fileResource
        // object can authenticate user access to the page.
        $path = parse_url($url, PHP_URL_PATH);
        $pathArray = explode('/', $path);
        $resourceId = SearchPathString::grabResourceId($pathArray, 1, 0, $this->conn);
        $fileResource->resourceId = $resourceId;
        $fileResource->requestPath = $url;
        $fileResource->resourceData = $data;

        $response = $fileResource->putAction();
        $this->resourceStatus = $fileResource->httpStatus;
        return $response;

    }


    /**
     * @param string $url
     * @param null|string $username
     * @param null|string $password
     * @param array $paramHeaders
     */
    function delete($url, $username = null, $password = null, $paramHeaders = array()) {

        $this->connectSql();

        // Instantiate fileResource and set dependencies.
        $fileResource = new FileResource();
        $fileResource->conn = $this->conn;
        // Acquire the page ID attributed to the URL so the fileResource
        // object can authenticate user access to the page.
        $path = parse_url($url, PHP_URL_PATH);
        $pathArray = explode('/', $path);
        $resourceId = SearchPathString::grabResourceId($pathArray, 1, 0, $this->conn);
        $fileResource->resourceId = $resourceId;
        $fileResource->requestPath = $url;

        $response = $fileResource->deleteAction();
        $this->resourceStatus = $fileResource->httpStatus;
        return $response;
    }


}
