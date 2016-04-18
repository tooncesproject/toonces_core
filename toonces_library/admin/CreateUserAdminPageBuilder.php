<?php

include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class CreateUserAdminPageBuilder extends AdminPageBuilder
{

	var $conn;

	function buildAdminTool() {

		// Make a copy block element for the top
		$html = <<<HTML
		<div class="utility_block">
		<h2>Create new user</h2>
		</div>
HTML;

	$createUserFormElement = new CreateUserFormElement($this->pageViewReference);
	$createUserFormElement->htmlHeader = '<div class="copy_block">'.PHP_EOL;
	$createUserFormElement->htmlFooter = '</div>'.PHP_EOL;

	$this->toolElement->addElement($createUserFormElement);

	}
	function setupPageBuilder() {
		$this->adminAccessOnly = 1;
	}

}