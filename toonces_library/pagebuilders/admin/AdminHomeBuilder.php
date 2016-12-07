<?php


class AdminHomeBuilder extends AdminPageBuilder
{

	// Override empty abstract function buildAdminTool()
	function buildAdminTool() {
		$this->toolElement = new Element($this->pageViewReference);
		$this->toolElement->setHTML($this->adminPageHTML());
	}

	function adminPageHTML() {

		$adminPageHTML = <<<HTML
            	<div class="copy_block">
						<a href="javascript: submitform()">Log Out</a>
                </div>


HTML;
		$nickname = isset($_SESSION['nickname']) ? $_SESSION['nickname'] : '';
		$adminPageHTML = sprintf($adminPageHTML, $nickname);

		return $adminPageHTML;
	}

	function setupPageBuilder() {
		$this->adminAccessOnly = false;
	}
}