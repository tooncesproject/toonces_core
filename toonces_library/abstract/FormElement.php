<?php

require_once ROOTPATH.'/toonces.php';

abstract class FormElement extends Element
{
	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;
	
	public function __construct($pageView) {
		$this->pageViewReference = $pageView;
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
	}
	
}
