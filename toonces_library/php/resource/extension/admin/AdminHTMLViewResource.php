<?php

include_once LIBPATH.'php/toonces.php';

class AdminHTMLViewResource extends HTMLViewResource implements iResource
{
	// instance variables
	// inherited class variables commented out
	//var $htmlHeader;
	//var $htmlFooter;
	//var $pageElements = array();
	//var $elementsCount = 0;
	

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
	
	public function getResource() {
			
		$pageString = "";
		
		$this->htmlHeader = $this->getTopHTML();
		$this->htmlFooter = $this->getBottomHTML();
		
		foreach($this->pageElements as $object) {
			$pageString = $pageString.$object->getResource().PHP_EOL;	
		}
		
		$htmlString = $this->htmlHeader.$pageString.$this->htmlFooter;
		return $htmlString;
		
	}

}

?>