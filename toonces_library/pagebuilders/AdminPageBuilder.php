<?php
require_once ROOTPATH.'/toonces.php';

// Admin tools includes
include_once ROOTPATH.'/admin/AdminHomeBuilder.php';
include_once ROOTPATH.'/admin/AdminViewElement.php';

abstract class AdminPageBuilder extends PageBuilder
{

	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	var $toolElement;
	var $adminAccessOnly;
	var $accessGranted;

	function buildPage() {

		//Set up customizations.
		$this->setupPageBuilder();

		// build page...
		$this->toolElement = new ViewElement($this->pageViewReference);
		$this->buildAdminTool();

		// Does user have access?
		$this->accessGranted = 0;
		if ($this->adminAccessOnly == 1) {
			if ($this->pageViewReference->sessionManager->userIsAdmin == 1) {
				$this->accessGranted = 1;
			} else {
				$this->accessGranted = $this->pageViewReference->userCanAccessAdminPage;
			}
		} else {
			$this->accessGranted = 1;
		}

		// If logged in, go to dashboard, otherwise go to login page
		if ($this->pageViewReference->sessionManager->adminSessionActive == 1) {
			$this->buildDashboardPage();
		} else {
			$this->buildLoginPage();
		}

		return $this->elementArray;

	}

	function buildLoginPage() {
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

		$bodyElement = new ViewElement($this->pageViewReference);

		$bodyTopElement = new Element($this->pageViewReference);
		$bodyTopElement->setHTML($this->loginPageHTMLTop());
		$loginFormElement = new LoginFormElement($this->pageViewReference);
		$bodyBottomElement = new Element($this->pageViewReference);
		$bodyBottomElement->setHTML($this->loginPageHTMLBottom());

		$bodyElement->addElement($bodyTopElement);
		$bodyElement->addElement($loginFormElement);
		$bodyElement->addElement($bodyBottomElement);

		array_push($this->elementArray, $bodyElement);

		$footerElement = new Element($this->pageViewReference);

		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/generic_html_footer.html'));

		array_push($this->elementArray, $footerElement);
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

		// Add logout form to body element
		$logoutFormElement = new LogoutFormElement($this->pageViewReference);
		$bodyElement->addElement($logoutFormElement);

		// Add admin nav element to body element
		$adminNavElement = new AdminNavElement($this->pageViewReference);
		$bodyElement->addElement($adminNavElement);

		if (!isset($this->toolElement)) {
			throw new Exception('Error: element $adminToolElement must be set before page is rendered.');
		} else if ($this->accessGranted == true) {
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


	function loginPageHTMLTop() {

		$topHTML = <<<HTML
<body>
	<div class="main_container">
    	<div class="content_container">
        	<div class="pageheader">
            </div>
            <div class="content_container">
            	<div class="copy_block">
                	<h1>
                    	Welcome to Toonces Admin. You're fucking awesome.
                    </h1>
                    <h2>
                    	Log in here, yo.
                    </h2>
HTML;


		return $topHTML;

	}

	function loginPageHTMLBottom() {

		$bottomHTML = <<<HTML
                	<p>
                   	</p>
                </div>
            </div>
        </div>
    </div>
</body>
HTML;

		return $bottomHTML;
	}

	function buildAdminTool() {
		// Add elements to admin tool.
	}

	function setupPageBuilder() {
		// Custom variable settings for inheriting classes go here.
	}


}