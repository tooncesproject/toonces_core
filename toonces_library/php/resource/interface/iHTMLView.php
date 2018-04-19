<?php
interface iHTMLView extends iResource
{

	public function setPageIsPublished($paramPageIsPublished);
	public function getPageIsPublished();
	
	public function checkUserCanEdit();

	// Inherited from iResource, commented for ease:
	//public function getResource();
}
