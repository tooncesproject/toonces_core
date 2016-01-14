<?php

class AccessRestrictedPage_old extends PageBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	var $nickname;

	function buildPage($pageView) {
		// build page...
		$this->pageViewReference = $pageView;

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

		$bodyElement = new Element($this->pageViewReference);

		$bodyElement->setHTML(sprintf($this->adminPageHTML(),$this->nickname));

		array_push($this->elementArray, $bodyElement);

		$footerElement = new Element($this->pageViewReference);

		$footerElement->setHTML(file_get_contents(ROOTPATH.'/static_data/generic_html_footer.html'));

		array_push($this->elementArray, $footerElement);
	}

	function adminPageHTML() {

		$adminPageHTML = <<<HTML

<script type="text/javascript">
function submitform()
{
  document.logoutForm.submit();
}
</script>

<body>

	<div class="main_container">
    	<div class="content_container">
        	<div class="pageheader">
            </div>
            <div class="content_container">
            	<div class="copy_block">
					<p>
					Hello, %s
					</p>
                	<h1>
                    	Toonces Admin Dashboard</h1>
					<h2>I'm sorry, Dave. I'm afraid i can't do that.</h2>
					<p>You don't have access to this tool. Contact the site administrator if you think you're special enough to use this.</p>
					<p><a href="/admin/">Back to Toonces Admin</a></p>
					</form>
					</p>
                    </div>
            </div>
        </div>
    </div>
</body>
</html>

HTML;

		return $adminPageHTML;
	}
}