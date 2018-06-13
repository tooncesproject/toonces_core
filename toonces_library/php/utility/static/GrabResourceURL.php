<?php
/*
 * 	Static Class:
 *	GrabResourceURL
 * 
 *	Paul Anderson 10/9/2015
 *
 * 	Gets a URL for a given Page ID.
 * 
 * 
 */
 
include_once LIBPATH.'php/toonces.php';

class GrabResourceURL {
	
	public static $conn;
	
	public static function getURL($resourceId, $paramSQLConn) {
		
		
		if(empty(GrabResourceURL::$conn)) {
			GrabResourceURL::$conn = $paramSQLConn;
		}
		
		return GrabResourceURL::getPathnames($resourceId, '');
		
	}
	
	
	public static function getPathnames($resourceId,$urlPath) {
		// This function recursively iterates through a page and all its ancestors,
		// Concatenating together their pathnames until it has generated the full URL.
		
		$sql = <<<SQL
        SELECT
        	pg.pathname
        	,rhb.resource_id AS ancestor_resource_id
        FROM
        	toonces.resource pg
        JOIN
        	toonces.resource_hierarchy_bridge rhb ON pg.resource_id = rhb.descendant_resource_id
        WHERE
        	pg.resource_id = :resourceId
SQL;

		$conn = GrabResourceURL::$conn;
		$stmt = $conn->prepare($sql);
		$stmt->execute(['resourceId' => $resourceId]);
		$result = $stmt->fetchAll();

		foreach($result as $row) {
				
			$pathname = $row['pathname'];
			$ancestorResourceId = $row['ancestor_resource_id'];
		}
		
		// If the page has an ancestor, continue recursion.
		// If the current iteration is for the home page, set ancestorResourceId to NULL
		// so the function can gracefully exit recursion.
		
		if (empty($ancestorResourceId)) {
			$ancestorResourceId = NULL;
		}

		if ($ancestorResourceId) {
			// add current page pathname to URL string
			$urlPath = '/'.$pathname.$urlPath;
			return GrabResourceURL::getPathnames($ancestorResourceId,$urlPath);
		} else {
			return $urlPath;
		}
		
		
	}
	
	
}