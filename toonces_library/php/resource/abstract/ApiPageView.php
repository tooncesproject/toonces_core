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
    public $pageLinkText;

    public $sessionManager;

    var $queryArray = array();
    var $headers = array();
    var $dataObjects = array();
    var $HTTPMethod;
    var $pageId;

    public $apiVersion;


    // iPageView setters and getters
    public function setPageURI($paramPageURI) {
        $this->pageURI = $paramPageURI;
    }

    public function addElement($element) {
        array_push($this->dataObjects, $element);
    }

    public function getPageURI() {
        if (!$this->pageURI)
            $this->setPageURI(GrabPageURL::getURL($this->pageId, $this->sqlConn));

            return $this->pageURI;
    }

    public function setSQLConn($paramSQLConn) {
        $this->sqlConn = $paramSQLConn;
    }

    public function getSQLConn() {
        return $this->sqlConn;
    }

    public function setPageLinkText($paramPageLinkText) {
        $this->pageLinkText = $paramPageLinkText;
    }

    public function getPageLinkText() {
        return $this->pageLinkText;
    }

    public function setPageTitle($paramPageTitle) {
        // Do nothing
    }

    public function getPageTitle() {
        // API pages do not have page titles.
        return NULL;
    }

    public function checkSessionAccess() {
        // REST APIs are stateless; therefore session access does not apply.
        // Defaults to True; any authentication is handled by the resource itself.
        return true;
    }

    public function checkAdminSession() {
        // Similarly, admin session does not apply.
        return false;
    }

    public function __construct($pageViewPageId) {
        $this->pageId = $pageViewPageId;
        // Detect API version
        $this->apiVersion = $_SERVER['HTTP_ACCEPT_VERSION'];
    }

    public function getResource() {
        // Overrides DataResource->getResource()
        // Validates the nested resource and returns it.

        // ApiPageView allows only a single root resource object; throw an exception if
        // this isn't the case.
        if (count($this->dataObjects) != 1)
            throw new Exception('Error: An APIPageBuilder must instantiate one and only one data object per page.');

            return $this->dataObjects[0];

    }

    public function renderPage() {
        // Not implemented in abstract class.
        // Called by index.php on objects compliant to the iPageView interface.
        // Subclasses should override this.
    }
}
