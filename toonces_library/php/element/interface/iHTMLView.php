<?php
interface iHTMLView extends iResource
{

	public function setPageTitle();
	public function getPageTitle();

	public function setPageIsPublished();
	public function getPageIsPublished();

	// Inherited from iResource, commented for ease:
	//public function getResource();
}
