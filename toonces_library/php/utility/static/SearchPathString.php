<?php
/*
 * @author paulanderson
 * SearchPathString.php
 *
 * Provides a static function that recursively searches for
 * a page ID attributed to a URL path.
 *
 */

require_once LIBPATH.'/php/toonces.php';

class SearchPathString {

    public static function grabResourceId($pathArray, $resourceId, $depthCount, $conn) {

        $pageFound = false;
        $descendantResourceId = null;

        $sql = <<<SQL
        SELECT
	       rhb.descendant_resource_id,
	       r.pathname
        FROM resource_hierarchy_bridge rhb
        LEFT JOIN resource r on r.resource_id = rhb.descendant_resource_id
        WHERE rhb.resource_id = :resourceId;
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);

        $descenantPages = $stmt->fetchAll();

        if (!$descenantPages) {
            return $resourceId;
        }

        foreach ($descenantPages as $row) {

            if ($row['pathname'] == $pathArray[$depthCount]) {
                $descendantResourceId = $row['descendant_resource_id'];
                $pageFound = true;
                break;
            }
        }
        // if a page was found and the end of the array has been reached, return the descendant ID
        // otherwise continue recursion
        $nextDepthCount = ++$depthCount;

        if ($pageFound && (!array_key_exists($nextDepthCount, $pathArray) OR trim($pathArray[$nextDepthCount]) == '')) {
            return $descendantResourceId;

        } else if ($pageFound) {
            //iterate recursion if page found
            return SearchPathString::grabResourceId($pathArray, $descendantResourceId, $nextDepthCount, $conn);

        } else {

            //if not found, query deepest page for whether it allows a redirect
            $query = 'SELECT redirect_on_error FROM toonces.resource WHERE resource_id = '.$resourceId;
            $result = $conn->query($query);

            foreach($result as $row) {
                $redirectOnError = $row['redirect_on_error'];
            }

            if ($redirectOnError) {
                return $resourceId;
            }
            else {
                return 0;
            }
        }
    }
}
