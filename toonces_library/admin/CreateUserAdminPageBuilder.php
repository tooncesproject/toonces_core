<?php

//include_once ROOTPATH.'/admin/AdminViewElement.php';
include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class CreateUserAdminPageBuilder extends AdminPageBuilder
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
		<h2>Create new user</h2>
		</div>
HTML;

	$topCopyBlock = new CreateUserFormElement($this->pageViewReference);
	//$topCopyBlock->setHTML($this->formHTML());

	$this->toolElement->addElement($topCopyBlock);
	
	}
	function setupPageBuilder() {
		$this->adminAccessOnly = 1;
	}

}