<?php

include_once LIBPATH.'php/toonces.php';

class AdminViewElement extends ViewElement implements iElement
{
	// instance variables
	// inherited class variables commented out
	//var $htmlHeader;
	//var $htmlFooter;
	//var $pageElements = array();
	//var $elementsCount = 0;
	
	//override element constructor method
//	public function __construct() {
		// do nothing
//	}
	
	
	// setter methods

	public function setHtmlHeader ($headerString) {
		// override with hard coded header
		$this->htmlHeader = $this->getTopHTML();
	}
	
	public function setHtmlFooter ($footerString) {
		// override with hard coded footer
		$this->htmlFooter = $this->getBottomHTML();
	}
	
	
	// HTML top
	private function getTopHTML() {
		
		$html = <<<HTML
<body>
	<div class="main_container">
    	<div class="content_container">
        	<div class="pageheader">
            </div>
            <div class="content_container">
            	<div class="copy_block">
						<p>
							Hello, %s
						</p>
                		<h1>Toonces Admin Dashboard</h1>
				</div>

HTML;
		
		$html = sprintf($html, $_SESSION['nickname']);
	
		return $html;
	}
	
	// HTML bottom
	private function getBottomHTML() {
	
		$html = <<<HTML
                	<br>
					<br>
                </div>
            </div>
        </div>
    </div>
</body>
	
HTML;
	
		return $html;
	}
	
	// execution method
	
	public function getHTML() {
			
		$pageString = "";
		
		$this->htmlHeader = $this->getTopHTML();
		$this->htmlFooter = $this->getBottomHTML();
		
		foreach($this->pageElements as $object) {
			$pageString = $pageString.$object->getHTML().PHP_EOL;	
		}
		
		$htmlString = $this->htmlHeader.$pageString.$this->htmlFooter;
		return $htmlString;
		
	}

}

?>