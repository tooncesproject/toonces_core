<?php

//include_once ROOTPATH.'/admin/AdminViewElement.php';
include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class AdminHomeBuilder extends AdminPageBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	
	// Override empty abstract function buildAdminTool()
	function buildAdminTool() {
		$this->toolElement = new Element($this->pageViewReference);
		$this->toolElement->setHTML($this->adminPageHTML());
	}

	function adminPageHTML() {
	
		$adminPageHTML = <<<HTML
            	<div class="copy_block">
                	<p><a href="/admin/useradmin">Manage Users</a></p>
					<p>Manage Pages</p>
                	<p>Manage Blogs</p>
                	<p>Get Weerd</p>
					<p>
					<!--
					<form name="logoutForm" method="post">
						<input type="hidden" name="logout" value="1" /> 
				-->
						<a href="javascript: submitform()">Log Out</a>
					</form>
					</p>
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