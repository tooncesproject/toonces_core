<?php

require_once LIBPATH.'toonces.php';

class PageView extends ViewElement implements iView
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

	public function __construct($pageViewPageId) {
		$this->pageId = $pageViewPageId;
		parse_str($_SERVER['QUERY_STRING'], $this->queryArray);
	}


	public function addElement ($element) {

		array_push($this->pageElements,$element);
		$this->elementsCount++;

	}

	//setter methods

	public function setPageTitle($pageTitleString) {
		$this->pageTitle = $pageTitleString;
	}

	public function setStyleSheet($styleSheetFile) {
		$this->styleSheet = $styleSheetFile;
	}

	public function setPageLinkText($pageLinkString) {
		$this->pageLinkText = $pageLinkString;
	}

	// getter methods
	public function getPageTitle() {
		return $this->pageTitle;
	}

	public function getStyleSheet() {
		return  $this->styleSheet;
	}

	public function getPageLinkText() {
		return $this->pageLinkText;
	}

	// execution methods

	public function getHTML() {

		$htmlString = $this->htmlHeader;

		foreach($this->pageElements as $object) {
			$htmlString = $htmlString.$object->getHTML();
		}

		return $htmlString;

	}

	public function renderPage() {

		echo $this->getHTML();

	}

}
