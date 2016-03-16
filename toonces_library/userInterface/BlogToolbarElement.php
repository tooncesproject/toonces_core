<?php
/*
 * BlogToolbarElement
 *
 * Initial Commit: Paul Anderson 2/7/2015
 *
 */

class BlogToolbarElement extends ToolbarElement
{

	// Page state
	var $pagePublished;

	// user state
	var $userCanEdit;

	public function buildToolElement() {

		// Tool's capabilities:
		// 	publish page
		//	unpublish page
		//  Create new blog post

		$this->newPostElement = new Element($this->pageViewReference);


		$newPostLinkHTML = '';

		$urlArray = parse_url($_SERVER['REQUEST_URI']);
		// Build URL for new post link
		$newpostUrlArray = $urlArray;
		$urlPath = $newpostUrlArray['path'];
		if (isset($urlArray['query']))
			parse_str($urlArray['query'], $params);

		// Add parameter
		$params['mode'] = 'newpost';
		$newpostUrlArray['query'] = http_build_query($params);
		$newPostURL = $newpostUrlArray['path'].'?'.$newpostUrlArray['query'];


		$htmlOut = <<<HTML
		<div class="TE_pagetoolelement">
			<p>Hey, that's a good lookin' blog you got there!</p>
HTML;

		$htmlOut = $htmlOut.PHP_EOL;

		// If the user has editing privileges, add a link create new post
		if ($this->userCanEdit == true)
			$newPostLinkHTML = '<p><a href="'.$newPostURL.'"><telink>Create a New Blog Post</telink></a></p>'.PHP_EOL;

		$htmlOut = $htmlOut.$newPostLinkHTML;

		$htmlOut = $htmlOut.'</div>'.PHP_EOL;

		$this->newPostElement->setHTML($htmlOut);
		$this->toolElement->addElement($this->newPostElement);
	}

}
