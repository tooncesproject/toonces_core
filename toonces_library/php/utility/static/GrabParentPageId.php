<?php
/**
 * GrabParentPageId.php
 * @author paulanderson
 * Initial commit: Paul Anderson, 5/2/2018
 *
 * Provides a static method for getting a page's parent ID from the page's ID.
 *
 */

require_once LIBPATH.'php/toonces.php';

class GrabParentPageId {

    public static function getParentId($pageId, $sqlConn) {
        /**
         * @param int $pageId - the pageId whose parent we want to find.
         * @param PDO $sqlConn - a PDO object providing database access.
         * @return int $parentPageId - The parent ID, null if there is none.
         */

        $sql = <<<SQL
        SELECT
            page_id
        FROM page_hierarchy_bridge
        WHERE descendant_page_id = :pageId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(['pageId' => $pageId]);
        $result = $stmt->fetchAll();

        $parentPageId = $result[0][0];
        return $parentPageId;

    }
}
