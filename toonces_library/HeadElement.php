<?php

include_once ROOTPATH.'/toonces.php';

class HeadElement extends Element implements iElement {

	//var $html;
	//var $htmlHeader;
	//var $htmlFooter;
	var $pageTitle;
	var $styleSheet;
	var $headTags;
	
	
	// setter methods
	public function setStyleSheet($styleSheetRef) {
		$this->styleSheet = $styleSheetRef;
	}
	
	public function setPageTitle($pageTitleString) {
		$this->pageTitle = $pageTitleString;
	}
	
	public function setHeadTags($headTagsString) {
		$this->headTags = $headTagsString;
	}
	
	// non-implemented setter method
	public function setHTML($htmlString) {
		//override as empty method
	}
	
	public function getHTML() {
		
		if(empty($this->htmlHeader))
			$this->htmlHeader = '<head>';
		
		if(empty($this->htmlFooter))
			$this->htmlFooter = '</head>';
		
		$this->html = $this->htmlHeader.PHP_EOL;
		$this->html = $this->html.'<title>'.$this->pageTitle.'</title>'.PHP_EOL;
		$this->html = $this->html.'<link href="/toonces_library/css/'.$this->styleSheet.'" rel="stylesheet" type="text/css" />'.PHP_EOL;
		$this->html = $this->html.$this->headTags.PHP_EOL;
		$this->html = $this->html.$this->htmlFooter.PHP_EOL;
		return $this->html;
	}
}
