<?php
interface iHTMLView extends iResource
{

	public function setPageTitle($paramPageTitle);
	public function getPageTitle();

	public function setPageIsPublished($paramPageIsPublished);
	public function getPageIsPublished();

	// Inherited from iResource, commented for ease:
	//public function getResource();
}
