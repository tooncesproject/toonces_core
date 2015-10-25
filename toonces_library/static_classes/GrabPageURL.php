<?php
/*
 * 	Static Class:
 *	GrabPageURL
 * 
 *	Paul Anderson 10/9/2015
 *
 * 	Gets a URL for a given Page ID.
 * 
 * 
 */
 
include_once ROOTPATH.'/toonces.php';

class GrabPageURL {
	
	private static $conn;
	
	public static function getURL($pageId) {
		
		
		if(empty(GrabPageURL::$conn)) {
			GrabPageURL::$conn = UniversalConnect::doConnect();
		}
		
		return GrabPageURL::getPathnames($pageId, '');
		
	}
	
	
	private static function getPathnames($pageId,$urlPath) {
		// This function recursively iterates through a page and all its ancestors,
		// Concatenating together their pathnames until it has generated the full URL.
		
		$query = sprintf(file_get_contents(ROOTPATH.'/sql/get_page_pathname_and_ancestor.sql'),$pageId);
		
		$result = GrabPageURL::$conn->query($query);
		
		foreach($result as $row) {
				
			$pathname = $row['pathname'];
			$ancestorPageId = $row['ancestor_page_id'];
		}
		
		// If the page has an ancestor, continue recursion.
		// If the current iteration is for the home page, set ancestorPageId to NULL
		// so the function can gracefully exit recursion.
		
		if (empty($ancestorPageId)) {
			$ancestorPageId = NULL;
		}

		if ($ancestorPageId) {
			// add current page pathname to URL string
			$urlPath = '/'.$pathname.$urlPath;
			return GrabPageURL::getPathnames($ancestorPageId,$urlPath);
		} else {
			return $urlPath;
		}
		
		
	}
	
	
}