<?php
require_once ROOTPATH.'/toonces.php';

// Admin tools includes
include_once ROOTPATH.'/admin/AdminHomeBuilder.php';
include_once ROOTPATH.'/admin/UserManager.php';
include_once ROOTPATH.'/admin/AdminViewElement.php';

class AdminPageBuilder extends PageBuilder
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
		
		// If logged in, go to dashboard, otherwise go to login page
		if (isset($this->pageViewReference->sessionManager)) {
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
		//array_push($this->elementArray,$loginFormElement);
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
	
		// To do: nuke this stuff, make it page based.
		
		// Default is admin home.
		$adminPageBuilder = 'AdminHomeBuilder';
	
		// But... If an admin tool is specified in the query string, go to that page.
		if (array_key_exists('admintool', $this->pageViewReference->queryArray)) {
			$adminPageBuilder = $this->pageViewReference->queryArray['admintool'];
			}

		$adminHomeBuilder = new $adminPageBuilder($this->pageViewReference);
		
		$this->elementArray = $adminHomeBuilder->buildPage($this->pageViewReference);
		
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
	
	
}