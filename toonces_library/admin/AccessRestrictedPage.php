<?php

//include_once ROOTPATH.'/admin/AdminViewElement.php';
include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class AccessRestrictedPage extends AdminToolBuilder
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
	
		$this->toolElement = new Element($this->pageViewReference);
		$this->toolElement->setHTML($this->adminPageHTML());
		
		$this->buildDashboardPage();
	
		return $this->elementArray;
	
	}

	function adminPageHTML() {
	
		$adminPageHTML = <<<HTML
            	<div class="copy_block">
                	<<h2>I'm sorry, Dave. I'm afraid i can't do that.</h2>
					<p>You don't have access to this tool. Contact the site administrator if you think you're special enough to use this.</p>
					<p><a href="/admin/">Back to Toonces Admin</a></p>
                </div>
				
	
HTML;
		$adminPageHTML = sprintf($adminPageHTML, $_SESSION['nickname']);
	
		return $adminPageHTML;
	}
}