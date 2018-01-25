<?php

require_once LIBPATH.'php/toonces.php';

class PageView extends ViewElement implements iResourceView, iHTMLView
{
	// instance variables

	var $htmlHeader;
	var $pageElements = array();
	var $pageTitle;
	var $styleSheet;
	var $pageLinkText;
	var $pageId;
	var $queryArray = array();
	var $sessionManager;
	var $logoutSignal;
	var $userCanEdit;
	var $userCanAccessAdminPage;
	var $pageTypeId;
	var $urlPath;
	var $pageIsPublished;
	var $conn;

	public function __construct($pageViewPageId) {
		$this->pageId = $pageViewPageId;
		parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
	}


	public function addElement ($element) {

		array_push($this->pageElements,$element);
		$this->elementsCount++;

	}

	// execution methods

	public function getResource() {

		$htmlString = $this->htmlHeader;

		foreach($this->pageElements as $object) {
			$htmlString = $htmlString.$object->getResource();
		}

		return $htmlString;

	}

	public function renderPage() {

		echo $this->getResource();

	}

}

