<?php
/*
 * BlogToolbarElement
 *
 * Initial Commit: Paul Anderson 2/7/2015
 * Refactored as derivative from BlogPostToolbarElement 4/29/2016.
 *
 */

class BlogToolbarElement extends ToolbarElement
{

	// Page state
	var $pagePublished;

	// user state
	var $userCanEdit;

	// utility stuff
	// Array of this page's parsed URI
	var $urlArray = array();
	// Array of this page's parsed url parameters
	var $currentParams = array();
	var $currentParamsMode;

	public function buildToolElement() {

		// Tool's capabilities:
		// 	publish page
		//	unpublish page

		// Set URL variables common to all tools
		$thisPageURI = $_SERVER['REQUEST_URI'];
		$this->urlArray = parse_url($thisPageURI);

		if (isset($this->urlArray['query'])) {
			parse_str($this->urlArray['query'], $this->currentParams);
		}

		$this->currentParamsMode = isset($this->currentParams['mode']) ? $this->currentParams['mode'] : '';

		// Build the new post tool
		$newPostToolElement = new Element($this->pageViewReference);
		$newPostToolElement->html = $this->buildNewpostToolElementHTML();
		$this->toolElement->addElement($newPostToolElement);



	}

	function buildNewpostToolElementHTML() {

		$editPostLinkHTML = '';

		if (isset($this->urlArray['query'])) {
			parse_str($this->urlArray['query'], $linkParams);
		}

		// Build URL for edit post link
		$editPostUrlArray = $this->urlArray;
		$urlPath = $editPostUrlArray['path'];

		// Add parameter
		$linkParams['mode'] = 'newpost';
		$editPostUrlArray['query'] = http_build_query($linkParams);
		$editPostURL = $editPostUrlArray['path'].'?'.$editPostUrlArray['query'];

		$editPageToolHTML= <<<HTML
		<div class="TE_pagetoolelement">
			<p>Hey, that's a good lookin' blog you got there!</p>
HTML;

		$editPageToolHTML = $editPageToolHTML.PHP_EOL;

		// If the user has editing privileges, add a blog editing link
		if ($this->userCanEdit == true) {
			// If we're in newpost mode, make it a 'cancel' link, otherwise an 'new post' link

			if ($this->currentParamsMode == 'newpost') {
				unset($linkParams['mode']);
				$editPostUrlArray['query'] = http_build_query($linkParams);
				if (empty($editPostUrlArray['query'])) {
					$cancelPostURL = $editPostUrlArray['path'];
				} else {
					$cancelPostURL = $editPostUrlArray['path'].'?'.$editPostUrlArray['query'];
				}
				$editPostLinkHTML = '<p><a href="'.$cancelPostURL.'"><telink>Discard changes and cancel</telink></a></p>'.PHP_EOL;
			} else {
				$editPostLinkHTML = '<p><a href="'.$editPostURL.'"><telink>Create a new blog post</telink></a></p>'.PHP_EOL;
			}
		}
		$editPageToolHTML = $editPageToolHTML.$editPostLinkHTML;

		$editPageToolHTML = $editPageToolHTML.'</div>'.PHP_EOL;

		return $editPageToolHTML;
	}

}