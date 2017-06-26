<?php
/*    woo
 * 
 * 
 * 
 * 
 */

include_once LIBPATH.'toonces.php';

class TagElement extends Element implements iElement {

	// inherited class variables commented out
	//var $html;
	//var $htmlHeader;
	//var $htmlFooter;
	public $htmlTag;

	public function __construct($tag) {
		$this->htmlTag = $tag;
		
		if (empty($this->htmlTag)) {
			$this->htmlHeader = "<p>".PHP_EOL;
			$this->htmlFooter = "</p>".PHP_EOL;
		} else {
			$this->htmlHeader = '<'.$this->htmlTag.'>'.PHP_EOL;
			$this->htmlFooter = '</'.$this->htmlTag.'>'.PHP_EOL;
		}
		
	}
	
	
	public function setHTML($htmlString) {
		$this->html = $htmlString;
	}
	
	public function getHTML() {
		
		$this->html = $this->htmlHeader.PHP_EOL.$this->html.PHP_EOL.$this->htmlFooter;
		
		return $this->html;
	}
}
