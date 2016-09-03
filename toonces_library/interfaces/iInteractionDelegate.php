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
	
	// Variable to hold the input array
	public $inputArray; 
	
	// Method handling form data processing
	public function processFormData() {
		
	}
}