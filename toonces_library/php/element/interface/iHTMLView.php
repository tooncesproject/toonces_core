<?php
interface iHTMLView extends iResource
{

	public function setPageTitle($paramPageTitle);
	public function getPageTitle();

	public function setPageIsPublished($paramPageIsPublished);
	public function getPageIsPublished();
	
	public function checkUserCanEdit();

	// Inherited from iResource, commented for ease:
	//public function getResource();
}
