<?php

// Initial commit: Paul Anderson, 1/23/2016
// Provides a data structure for each page and its relationship to its parents
// It is a utility to help NavElement subclasses build a hierarchial array of links. 
class DynamicNavigationLink
{

	var $linkId;
	var $pageId;
	var $path;
	var $pageLinkText;
	var $ancestorPageId;
	var $descendantPageId;

	function __construct
	(
		 $paramLinkId
		,$paramPageId
		,$paramPath
		,$paramPageLinkText
		,$paramAncestorPageId
		,$paramDescendantPageId
	)
	{
		$this->linkId = $paramLinkId;
		$this->pageId = $paramPageId;
		$this->path = $paramPath;
		$this->pageLinkText = $paramPageLinkText;
		$this->ancestorPageId = $paramAncestorPageId;
		$this->descendantPageId = $paramDescendantPageId;
	}
}