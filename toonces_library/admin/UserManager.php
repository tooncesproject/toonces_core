<?php

//include_once ROOTPATH.'/admin/AdminViewElement.php';
include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class UserManager extends AdminToolBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	
	function buildPage($pageView) {
		// build page...
		$this->pageViewReference = $pageView;
		
		// This one is admin access only...
		$this->adminAccessOnly = 1;
	
		$this->toolElement = new Element($this->pageViewReference);
		$this->toolElement->setHTML($this->adminPageHTML());
		
		$this->buildDashboardPage();
	
		return $this->elementArray;
	
	}

	function adminPageHTML() {
	
		$adminPageHTML = <<<HTML
            	<div class="copy_block">
                	<p>Manage Pages</p>
                	<p>Manage Blogs</p>
                	<p>Get Weerd</p>
					<p>
					<form name="logoutForm" method="post">
						<input type="hidden" name="logout" value="1" />
						<a href="javascript: submitform()">Log Out</a>
					</form>
					</p>
					<p> hey this is the user manager page! </p>
                </div>
				
	
HTML;
		$adminPageHTML = sprintf($adminPageHTML, $_SESSION['nickname']);
	
		return $adminPageHTML;
	}
}