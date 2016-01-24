<?php
// Initial Commit: Paul Anderson, 1/23/2016
abstract class NavElement extends Element implements iElement
{
	var $userId;
	var $conn;
	var $linkArray = array();
	var $linkPages = array();

	
	function getHTML() {
		
		$html = '<div class="dynamic_nav">'.PHP_EOL;

		$this->buildLinkArray();
		// Beginning with the home page ID, recurse through the array,
		// building links hierarchially.
		$html = $html.$this->buildLinkHierarchy(0);
		
		$html = $html.'</div>';
		return $html;
	}

	function buildLinkHierarchy($linkOrdinal) {
		// Build link for the parent page
		$parentLinkObject = $this->linkArray[$linkOrdinal];
		$linkId = $parentLinkObject->linkId;
		$pageId = $parentLinkObject->pageId;
		$linkURL = $parentLinkObject->path;
		$linkText = $parentLinkObject->pageLinkText;
		$html = '';
		
		// Check to see if the link is already accounted for.
		if (array_key_exists($pageId, $this->linkPages) == false) {
			$html = '<p class="nav_link_parent"><a href="'.$linkURL.'">'.$linkText.'</a>'.PHP_EOL;
			$this->linkPages[$pageId] = $linkId;
			// Loop through the array for any descendants the page may have.
			foreach($this->linkArray as $childLinkObject) {

				$childLinkId = $childLinkObject->linkId;
				$childLinkPageId = $childLinkObject->pageId;
				$childLinkUrl = $childLinkObject->path;
				$childLinkText = $childLinkObject->pageLinkText;
				$childLinkDescendant = $childLinkObject->descendantPageId;

				if ($childLinkObject->ancestorPageId == $pageId) {

					// If the child page has no descendant, create its link
					if ($childLinkDescendant == 0) {
						$html = $html.'<p class="nav_link_child"><a href="'.$childLinkUrl.'">'.$childLinkText.'</a></p>'.PHP_EOL;
						$this->linkPages[$childLinkPageId] = $childLinkId;

					// If the child page has any descendants, recurse the function.
					} else {
						$html = $html.$this->buildLinkHierarchy($childLinkId);
					}
				}
			}
			$html = $html.'</p>'.PHP_EOL;
		}
		
		
		return $html;
	}
	
	function buildLinkArray() {
		// do stuff
	}
}