<?php
/**
 * @author paulanderson
 *
 * ApiPageView.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Abstract class providing common functionality for API PageView classes.
 *
 */

require_once LIBPATH.'php/toonces.php';

abstract class ApiPageView
{

    // iPageView interface vars
    public $pageURI;
    public $sqlConn;

    public $sessionManager;

    var $queryArray = array();
    var $headers = array();
    var $dataObjects = array();
    var $HTTPMethod;
    var $resourceId;

    public $apiVersion;


    // iPageView setters and getters

    public function setResource($paramResource) {
        array_push($this->dataObjects, $paramResource);
    }

    public function setSQLConn($paramSQLConn) {
        $this->sqlConn = $paramSQLConn;
    }

    public function getSQLConn() {
        return $this->sqlConn;
    }

    public function __construct($pageViewResourceId) {
        $this->resourceId = $pageViewResourceId;
        // Detect API version
        $this->apiVersion = $_SERVER['HTTP_ACCEPT_VERSION'];
    }

    public function getResource() {
        // Overrides DataResource->getResource()
        // Validates the nested resource and returns it.

        // ApiPageView allows only a single root resource object; throw an exception if
        // this isn't the case.
        if (count($this->dataObjects) != 1)
            throw new Exception('Error: An APIPageBuilder must instantiate one and only one data object per resource.');

            return $this->dataObjects[0];

    }

    public function renderResource() {
        // Not implemented in abstract class.
        // Called by index.php on objects compliant to the iPageView interface.
        // Subclasses should override this.
    }
}
