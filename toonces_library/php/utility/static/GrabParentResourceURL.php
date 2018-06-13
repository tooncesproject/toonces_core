<?php
/*
 * 	Static Class:
 *	GrabParentResourceURL
 * 
 *	Paul Anderson 10/9/2015
 *
 * 	Gets a URL for a given Page ID's parent page.
 * 
 * 
 */
 
include_once LIBPATH.'php/toonces.php';

class GrabParentResourceURL extends GrabResourceURL
{
	
	public static function getURL($resourceId, $paramSQLConn) {

		$ancestorResourceId = 1;

		if(empty(GrabResourceURL::$conn)) {
			GrabResourceURL::$conn = $paramSQLConn;
		}

		// Get page's ancestor ID.
        $sql = <<<SQL
        SELECT
        	pg.pathname
        	,rhb.resource_id AS ancestor_resource_id
        FROM
        	toonces.resource pg
        JOIN
        	toonces.resource_hierarchy_bridge rhb ON pg.resource_id = rhb.descendant_resource_id
        WHERE
        	pg.resource_id = :resourceId;
SQL;

		$conn = GrabResourceURL::$conn;
		$stmt = $conn->prepare($sql);
		$stmt->execute(['resourceId' => $resourceId]);
		$result = $stmt->fetchAll();

		//$query = sprintf($sql,$resourceId);
		
		//$result = GrabResourceURL::$conn->query($query);
		
		foreach($result as $row) {
			$ancestorResourceId = $row['ancestor_resource_id'];
		}

		if (empty($ancestorResourceId)) {
			return '/';
		} else {
			return self::getPathnames($ancestorResourceId, '');
		}
		
	}
	
}