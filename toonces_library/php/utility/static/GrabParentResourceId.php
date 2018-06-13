<?php
/**
 * GrabParentResourceId.php
 * @author paulanderson
 * Initial commit: Paul Anderson, 5/2/2018
 *
 * Provides a static method for getting a page's parent ID from the page's ID.
 *
 */

require_once LIBPATH.'php/toonces.php';

class GrabParentResourceId {

    public static function getParentId($resourceId, $sqlConn) {
        /**
         * @param int $resourceId - the resourceId whose parent we want to find.
         * @param PDO $sqlConn - a PDO object providing database access.
         * @return int $parentResourceId - The parent ID, null if there is none.
         */

        $sql = <<<SQL
        SELECT
            resource_id
        FROM resource_hierarchy_bridge
        WHERE descendant_resource_id = :resourceId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
        $result = $stmt->fetchAll();

        $parentResourceId = $result[0][0];
        return $parentResourceId;
        

    }
}
