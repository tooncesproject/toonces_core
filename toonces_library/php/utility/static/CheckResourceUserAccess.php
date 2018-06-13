<?php
/**
 * @author paulanderson
 * CheckResourceUserAccess.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Class providing a static method testing whether a user has access to a particular page.
 *
 */

class CheckResourceUserAccess {


    public static function checkUserAccess($userId, $resourceId, $sqlConn, $checkWriteAccess = false ) {
        // Tests whether an authenticated user has access to the resource's resourceId.
        $sql = <<<SQL
                SELECT
                    CASE WHEN
                        u.is_admin = TRUE
                        OR rua.user_id IS NOT NULL
                        OR r.published = TRUE
                        THEN TRUE
                    ELSE FALSE END AS read_access
                   ,CASE WHEN
                        u.is_admin = TRUE
                        OR rua.can_edit = TRUE
                        THEN TRUE
                     ELSE FALSE END AS write_access
                FROM users u
                JOIN resource r ON r.resource_id = :resourceId
                LEFT JOIN resource_user_access rua ON rua.user_id = u.user_id AND rua.resource_id = :resourceId
                WHERE u.user_id = :userId
SQL;
        $stmt = $sqlConn->prepare($sql);
        $sqlParams = array('userId' => $userId, 'resourceId' => $resourceId);
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
