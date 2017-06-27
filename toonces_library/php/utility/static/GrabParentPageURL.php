<?php
/*
 * 	Static Class:
 *	GrabParentPageURL
 * 
 *	Paul Anderson 10/9/2015
 *
 * 	Gets a URL for a given Page ID's parent page.
 * 
 * 
 */
 
include_once LIBPATH.'php/toonces.php';

class GrabParentPageURL extends GrabPageURL
{
	
	public static function getURL($pageId) {

		$ancestorPageId = 1;

		if(empty(GrabPageURL::$conn)) {
			GrabPageURL::$conn = UniversalConnect::doConnect();
		}

		// Get page's ancestor ID.
		$query = sprintf(file_get_contents(LIBPATH.'sql/query/get_page_pathname_and_ancestor.sql'),$pageId);
		
		$result = GrabPageURL::$conn->query($query);
		
		foreach($result as $row) {
		
			$pathname = $row['pathname'];
			$ancestorPageId = $row['ancestor_page_id'];
		}

		if (empty($ancestorPageId)) {
			return '/';
		} else {
			return self::getPathnames($ancestorPageId, '');
		}
		
	}
	
}