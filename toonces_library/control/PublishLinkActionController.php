<?php
/*
 * PublishLinkActionController
 * Initial commit: Paul Anderson, 2016-03-13
 * 
 * 		If the linkaction=publishpage variable is set in the query string,
 * 		this LinkActionController publishes the page.
 * 		
 */
 
class PublishLinkActionController extends LinkActionController
{
	
	var $conn;
	
	public function LinkAction() {
		
		// This function is only called if the page is unpublished.
		
		// Set connection
		if (isset($this->conn) == false) {
			$this->conn = UniversalConnect::doConnect();
		}
		
		// publish the page 
		
		
	}
	
}