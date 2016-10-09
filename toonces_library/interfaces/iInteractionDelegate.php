<?php
/*
 * iInteractionDelegate
 * Initial commit: Paul Anderson, 9/2/2015
 * Defines interface for interaction delegate classes.
 * 
 */

require_once LIBPATH.'toonces.php';

interface  iInteractionDelegate
{

	public function buildInputArray();
		// This function holds customizations for building the form array.
		// Its responsibility is to add members to the formArray[] instance variable.
		// FormElementInput objects will be rendered in the order they are added here.


	public function processFormData();
		// Method handling form data processing

}