<?php

include_once ROOTPATH.'/admin/AdminViewElement.php';
class AdminToolBuilder extends PageBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	var $toolElement;
	var $adminAccessOnly;
	
	
	function buildPage() {
		// build page...

		$this->buildDashboardPage();
	
		return $this->elementArray;
	
	}
	
	function buildDashboardPage() {
		// get static/generic html header, create as element
		$htmlHeaderElement = new Element($this->pageViewReference);
		$htmlHeaderElement->setHTML(file_get_contents(ROOTPATH.'/static_data/generic_html_header.html'));
	
		array_push($this->elementArray, $htmlHeaderElement);
	
		$headElement = new HeadElement($this->pageViewReference);
	
		// get head attributes
		$headElement->setPageTitle($this->pageViewReference->getPageTitle());
		$headElement->setStyleSheet($this->pageViewReference->getStyleSheet());
	
		$headElement->setHeadTags(file_get_contents(ROOTPATH.'/static_data/head_tags.html'));
	
		array_push($this->elementArray, $headElement);
	
		$bodyElement = new AdminViewElement($this->pageViewReference);

		// manage access
		//default to restricted 
		$accessGranted = false;
		$accessIsRestricted = isset($this->adminAccessOnly) ? $this->adminAccessOnly : false;
		
		if ($accessIsRestricted == false) {
			$accessGranted = true;
		} else if ($_SESSION['userIsAdmin'] == 1) {
			$accessGranted = true;
		}
		
		if (!isset($this->toolElement)) {
			throw new Exception('Error: element $adminToolElement must be set before page is rendered.');
		} else if ($accessGranted == true) {
			$bodyElement->addElement($this->toolElement);
		} else {
			$bodyElement->addElement($this->notifyPageRestricted());
		}
		
		array_push($this->elementArray, $bodyElement);
	
		$footerElement = new Element($this->pageViewReference);
	
		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/generic_html_footer.html'));
	
		array_push($this->elementArray, $footerElement);
	}
	
	function notifyPageRestricted() {
	
		$html = <<<HTML
            	<div class="copy_block">
                	<h2>I'm sorry, Dave, er, %s. I'm afraid i can't do that.</h2>
					<p>You don't have access to this tool. Contact the site administrator if you think you're special enough to use this.</p>
					<p><a href="/admin/">Back to Toonces Admin</a></p>
                </div>

HTML;
		$html = sprintf($html, $_SESSION['nickname']);
	
		$notifyElement = new Element($this->pageViewReference);
		$notifyElement->setHTML($html);
		return $notifyElement;
		
	}
	
}