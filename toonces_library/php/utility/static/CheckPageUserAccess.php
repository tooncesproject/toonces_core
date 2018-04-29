<?php
/**
 * @author paulanderson
 * CheckPageUserAccess.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Class providing a static method testing whether a user has access to a particular page.
 *
 */

class CheckPageUserAccess {

    public static function checkUserAccess($userId, $pageId, $sqlConn) {
        // Tests whether an authenticated user has access to the resource's pageId.
        $sql = <<<SQL
                SELECT
                    CASE WHEN
                        u.is_admin = TRUE
                        OR pua.user_id IS NOT NULL
                        OR p.published = TRUE THEN TRUE
                    ELSE FALSE END
                FROM users u
                JOIN pages p ON p.page_id = :pageId
                LEFT JOIN page_user_access pua ON pua.user_id = u.user_id AND pua.page_id = :pageId
                WHERE u.user_id = :userId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(array('userId' => $userId, 'pageId' => $pageId));
        $result = $stmt->fetchAll();
        $resultStr = $result[0][0];
        $userHasAccess = !empty($resultStr);

        return $userHasAccess;
    }
}
