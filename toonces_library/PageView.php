<?php
include_once ROOTPATH.'/interfaces/iView.php';
include_once ROOTPATH.'/interfaces/iElement.php';
include_once ROOTPATH.'/ViewElement.php';

class PageView extends ViewElement implements iView
{
	// instance variables

	// inherited variables commented out
	//private $htmlHeader;
	//private $htmlFooter;
	var $styleSheet;
	var $metaTag;
	var $pageTitle;
	//var $pageElements = array();
	
		
	
	public function addElement ($element) {
		
		array_push($this->pageElements,$element);
		$this->elementsCount++;
		
	}
	
	// execution methods
	
	public function getHTML() {
		
		$htmlString = "";
		
		foreach($this->pageElements as $object) {
			$htmlString = $htmlString.$object->getHTML();
		}
		
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
			"bodyCoseTag" => '</body>'.PHP_EOL,
			"htmlFooter" => $this->htmlFooter.PHP_EOL
		];
		
		return implode($htmlArray); 
		
	}
	
	
	
	public function renderPage() {

		echo $this->getHTML();
	}
	
}

?>