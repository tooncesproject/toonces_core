<?php
/*
 * BlogPostToolbarElement
 * 
 * Initial Commit: Paul Anderson 2/21/2015
 * 
 */

class BlogPostToolbarElement extends ToolbarElement
{

	// Page state
	var $pagePublished;

	// user state
	var $userCanEdit;

	public function buildToolHTML() {

		// Tool's capabilities:
		// 	publish page
		//	unpublish page
		//  Delete page
		//  recover deleted page

		$editPostLinkHTML = '';
		$publishHTML = '';

		$thisPageURI = $_SERVER['REQUEST_URI'];
		$urlArray = parse_url($thisPageURI);
		// Build URL for edit post link
		$editPostUrlArray = $urlArray;
		$urlPath = $editPostUrlArray['path'];
		if (isset($urlArray['query'])) {
			parse_str($urlArray['query'], $currentParams);
			parse_str($urlArray['query'], $linkParams);
		}
		// Add parameter
		$linkParams['mode'] = 'edit';
		$editPostUrlArray['query'] = http_build_query($linkParams);
		$editPostURL = $editPostUrlArray['path'].'?'.$editPostUrlArray['query'];

		$htmlOut = <<<HTML
		<div class="TE_pagetoolelement">
			<p>Hi. I'm a blog post.</p>
HTML;

		$htmlOut = $htmlOut.PHP_EOL;

		// If the user has editing privileges, add a blog editing link
		if ($this->userCanEdit == true) {
			// If we're in edit mode, make it a 'cancel' link, otherwise an 'edit' link
			$currentParamsMode = isset($currentParams['mode']) ? $currentParams['mode'] : '';
			if ($currentParamsMode == 'edit') {
				unset($linkParams['mode']);
				$editPostUrlArray['query'] = http_build_query($linkParams);
				if (empty($editPostUrlArray['query'])) {
					$cancelPostURL = $editPostUrlArray['path'];
				} else {
					$cancelPostURL = $editPostUrlArray['path'].'?'.$editPostUrlArray['query'];
				}
				$editPostLinkHTML = '<p><a href="'.$cancelPostURL.'"><telink>Discard changes and cancel editing</telink></a></p>'.PHP_EOL;
			} else {
				$editPostLinkHTML = '<p><a href="'.$editPostURL.'"><telink>Edit this blog post</telink></a></p>'.PHP_EOL;
			}
		}
		$htmlOut = $htmlOut.$editPostLinkHTML;

		$htmlOut = $htmlOut.'</div>'.PHP_EOL;
		
		// If not in edit or urlcheck mode, display a link to publish or unpublish the page.

		return $htmlOut;
	}

}