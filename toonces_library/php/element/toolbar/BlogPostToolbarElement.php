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

		// Build the edit/cancel edit post tool
		$editToolElement = new Element($this->pageViewReference);
		$editToolElement->html = $this->buildEditToolElementHTML();
		$this->toolElement->addElement($editToolElement);

		// If not in edit or urlcheck mode, display a link to publish or unpublish the page and the delete tool.
		$doDisplayPublishTool = ($this->currentParamsMode != 'edit') && ($this->currentParamsMode != 'urlcheck') && ($this->userCanEdit == true);
		if ($doDisplayPublishTool) {

			$publishLinkControlElement = new PublishLinkControlElement($this->pageViewReference);
			$this->toolElement->addElement($publishLinkControlElement);

			$unPublishLinkConrolElement = new UnPublishLinkControlElement($this->pageViewReference);
			$this->toolElement->addElement($unPublishLinkConrolElement);

			$publishToolElement = new Element($this->pageViewReference);
			$publishToolElement->html = $this->buildPublishToolElementHTML();
			$this->toolElement->addElement($publishToolElement);

			$deleteToolElement = new Element($this->pageViewReference);
			$deleteToolElement->html = $this->buildDeleteToolElementHTML();
			$this->toolElement->addElement($deleteToolElement);

		}


	}

	function buildEditToolElementHTML() {

		$editPostLinkHTML = '';

		if (isset($this->urlArray['query'])) {
			parse_str($this->urlArray['query'], $linkParams);
		}

		// Build URL for edit post link
		$editPostUrlArray = $this->urlArray;
		$urlPath = $editPostUrlArray['path'];

		// Add parameter
		$linkParams['mode'] = 'edit';
		$editPostUrlArray['query'] = http_build_query($linkParams);
		$editPostURL = $editPostUrlArray['path'].'?'.$editPostUrlArray['query'];

		$editPageToolHTML= <<<HTML
		<div class="TE_pagetoolelement">
			<p>Hi. I'm a blog post.</p>
HTML;

		$editPageToolHTML = $editPageToolHTML.PHP_EOL;

		// If the user has editing privileges, add a blog editing link
		if ($this->userCanEdit == true) {
			// If we're in edit mode, make it a 'cancel' link, otherwise an 'edit' link

			if ($this->currentParamsMode == 'edit') {
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
		$editPageToolHTML = $editPageToolHTML.$editPostLinkHTML;

		$editPageToolHTML = $editPageToolHTML.'</div>'.PHP_EOL;

		return $editPageToolHTML;
	}


	function buildPublishToolElementHTML() {

		$linkHTML = '';
		$linkParams = $this->currentParams;
		$publishPageToolHTML = '<div class="TE_pagetoolelement">'.PHP_EOL;

		// If page is unpublished, add the option to publish the page.
		if ($this->pageViewReference->pageIsPublished == false) {
			$publishedMessage = '<p>This blog post is not yet published.<br>Only you and the site administrator can see it.</p>'.PHP_EOL;
			$linkParams['linkaction'] = 'publishpage';
			$link = $this->urlArray['path'].'?'.http_build_query($linkParams);
			$linkHTML = '<p><a href="'.$link.'"><telink>Publish Page<telink></a></p>';
		} else {
		// If page is published, add the option to unpublish the page.
			$publishedMessage = '<p>This blog post is published, for the whole world too see!<br> How marvelous!</p>'.PHP_EOL;
			$linkParams['linkaction'] = 'unpublishpage';
			$link = $this->urlArray['path'].'?'.http_build_query($linkParams);
			$linkHTML = '<p><a href="'.$link.'"><telink>Unpublish Page</telink></a></p>';
		}

		$publishPageToolHTML = $publishPageToolHTML.$publishedMessage.PHP_EOL;
		$publishPageToolHTML = $publishPageToolHTML.$linkHTML.PHP_EOL;
		$publishPageToolHTML = $publishPageToolHTML.'</div>'.PHP_EOL;
		return $publishPageToolHTML;
	}

	function buildDeleteToolElementHTML() {

		$linkParams = $this->currentParams;
		$linkParams['mode'] = 'delete';
		$link = $this->urlArray['path'].'?'.http_build_query($linkParams);
		$DeleteToolHTML = <<<HTML
		<div class="TE_pagetoolelement">
			<p>Do you hate this blog post? You can delete it.</p> 
			<p><a href="%s"><telink>Delete post.</telink></a></p>
		</div>
HTML;

		$DeleteToolHTML = sprintf($DeleteToolHTML,$link);

		return $DeleteToolHTML;
	}
}