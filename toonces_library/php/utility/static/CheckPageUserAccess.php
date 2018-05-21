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


    public static function checkUserAccess($userId, $pageId, $sqlConn, $checkWriteAccess = false ) {
        // Tests whether an authenticated user has access to the resource's pageId.
        $sql = <<<SQL
                SELECT
                    CASE WHEN
                        u.is_admin = TRUE
                        OR pua.user_id IS NOT NULL
                        OR p.published = TRUE
                        THEN TRUE
                    ELSE FALSE END AS read_access
                   ,CASE WHEN
                        u.is_admin = TRUE
                        OR pua.can_edit = TRUE
                        THEN TRUE
                     ELSE FALSE END AS write_access
                FROM users u
                JOIN pages p ON p.page_id = :pageId
                LEFT JOIN page_user_access pua ON pua.user_id = u.user_id AND pua.page_id = :pageId
                WHERE u.user_id = :userId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $sqlParams = array('userId' => $userId, 'pageId' => $pageId);
        $stmt->execute($sqlParams);
        $result = $stmt->fetchAll();
        $readAccessStr = '';
        $writeAccessStr = '';
        if ($result) {
            $readAccessStr = $result[0][0];
            $writeAccessStr = $result[0][1];
        }
        $userHasReadAccess = !empty($readAccessStr);
        $userHasWriteAccess = !empty($writeAccessStr);

        if ($checkWriteAccess === true) {
            return $userHasWriteAccess;
        } else {
            return $userHasReadAccess;
        }

    }
}
