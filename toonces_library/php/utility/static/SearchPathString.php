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

    public static function grabPageId($pathArray, $pageId, $depthCount, $conn) {

        $pageFound = false;
        $descendantPageId;

        //$query = sprintf(file_get_contents(LIBPATH.'/sql/query/retrieve_child_page_ids.sql'),$pageid);
        $sql = <<<SQL
        SELECT
	       phb.descendant_page_id,
	       pg.pathname
        FROM page_hierarchy_bridge phb
        LEFT JOIN pages pg on pg.page_id = phb.descendant_page_id
        WHERE phb.page_id = :pageId;
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pageId' => $pageId]);

        $descenantPages = $stmt->fetchAll();

        if (!$descenantPages) {
            return $pageId;
        }

        foreach ($descenantPages as $row) {

            if ($row['pathname'] == $pathArray[$depthCount]) {
                $descendantPageId = $row['descendant_page_id'];
                $pageFound = true;
                break;
            }
        }
        // if a page was found and the end of the array has been reached, return the descendant ID
        // otherwise continue recursion
        $nextDepthCount = ++$depthCount;

        if ($pageFound && (!array_key_exists($nextDepthCount, $pathArray) OR trim($pathArray[$nextDepthCount]) == '')) {
            return $descendantPageId;

        } else if ($pageFound) {
            //iterate recursion if page found
            return $this->grabPageId($pathArray, $descendantPageId, $nextDepthCount, $conn);

        } else {

            //if not found, query deepest page for whether it allows a redirect
            $query = 'SELECT redirect_on_error FROM toonces.pages WHERE page_id = '.$pageId;
            $result = $conn->query($query);

            foreach($result as $row) {
                $redirectOnError = $row['redirect_on_error'];
            }

            if ($redirectOnError) {
                return $pageId;
            }
            else {
                return 0;
            }
        }
    }
}
