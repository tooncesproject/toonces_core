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

		$newPostLinkHTML = '';
		$publishHTML = '';

		$urlArray = parse_url($_SERVER['REQUEST_URI']);
		// Build URL for new post link
		$newpostUrlArray = $urlArray;
		$urlPath = $newpostUrlArray['path'];
		if (isset($urlArray['query']))
			parse_str($urlArray['query'], $params);

		// Add parameter
		$params['mode'] = 'newpost';
		$newpostUrlArray['query'] = http_build_query($params);
		$editPostURL = $newpostUrlArray['path'].'?'.$newpostUrlArray['query'];


		$htmlOut = <<<HTML
		<div class="TE_pagetoolelement">
			<p>Hi. I'm a blog post.</p>
HTML;

		$htmlOut = $htmlOut.PHP_EOL;

		// If the user has editing privileges, add a link create new post
		if ($this->userCanEdit == true) {
			// Create links to publish or unpublish the post.
			
			$newPostLinkHTML = '<p><a href="'.$editPostURL.'"><telink>Edit this post.</telink></a></p>'.PHP_EOL;

		}
		$htmlOut = $htmlOut.$newPostLinkHTML;

		$htmlOut = $htmlOut.'</div>'.PHP_EOL;

		return $htmlOut;
	}

}