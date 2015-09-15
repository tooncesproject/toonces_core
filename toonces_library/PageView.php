<?php
include_once ROOTPATH.'/interfaces/iView.php';
include_once ROOTPATH.'/interfaces/iElement.php';
include_once ROOTPATH.'/ViewElement.php';

class PageView extends ViewElement implements iView
{
	// instance variables

	var $htmlHeader;
	var $pageElements = array();
	var $pageTitle;
	var $styleSheet;
	var $pageLinkText;
		
	
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
		
		
		/*
		$htmlArray = [
			"header" => $this->htmlHeader,
			"headOpenTag" => '<head>'.PHP_EOL,
			"metaTag" => $this->metaTag.PHP_EOL,
			"titleTag" => '<title>'.$this->pageTitle.'</title>'.PHP_EOL,
			"stylesheetTag" => '<link href="'.$this->styleSheet.'" rel="stylesheet" type="text/css" />'.PHP_EOL,
			"faviconTag" => '<link rel="icon" type="image/ico" href="/favicon.ico">'.PHP_EOL,
			"headCloseTag" => '</head>'.PHP_EOL,
			"bodyOpenTag" => '<body>'.PHP_EOL,
			"elementsContent" => $htmlString.PHP_EOL,
			"bodyCloseTag" => '</body>'.PHP_EOL,
			"htmlFooter" => $this->htmlFooter.PHP_EOL
		];
		
		return implode($htmlArray); 
		*/
		
		return $htmlString;
		
		
	}
	
	
	
	public function renderPage() {

		echo $this->getHTML();
	}
	
}

?>