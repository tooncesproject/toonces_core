<?php


class EditPageAdminPageBuilder extends AdminPageBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	//var $toolElement;
	//var $adminAccessOnly;
	var $conn;

	function buildAdminTool() {
				
		// Make a copy block element for the top
		$html = <<<HTML
		<div class="utility_block">
			<h2>Sorry, this tool doesn't exist yet.</h2>
			<p><a href="/admin">Back to Toonces Admin</a></p> 
		</div>
HTML;

	$topCopyBlock = new HTMLResource($this->pageViewReference);
	
	$topCopyBlock->html = $html;

	$this->toolElement->addElement($topCopyBlock);
	
	}
	function setupPageBuilder() {
		$this->adminAccessOnly = 1;
	}

}