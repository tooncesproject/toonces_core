<?php
/**
 * @author paulanderson
 * CheckPageUserAccessTest.php
 * Initial Commit: Paul Anderson, 4/27/2018
 *
 * Unit test for the CheckUserAccess static utility.
 *
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class CheckPageUserAccessTest extends SqlDependentTestCase {

    public function testCheckUserAccess() {
        // ARRANGE
        // This requires a database fixture with an admin and non-admin user.
        $conn = $this->getConnection();
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        $nonAdminUserId = $this->createNonAdminUser();

        $sql = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('email' => $GLOBALS['TOONCES_USERNAME']));
        $result = $stmt->fetchAll();
        $adminUserId = $result[0][0];

        // Create some pages
        $unpublishedResourceId = $this-> createPage(false);
        $publishedResourceId = $this->createPage(true);
        $accessGrantedResourceId = $this-> createPage(false);
        $writeGrantedResourceId = $this-> createPage(false);

        // Create explicit RUA records
        $sql = <<<SQL
        INSERT INTO resource_user_access
            (resource_id, user_id, can_edit)
        VALUES
            (:resourceId, :userId, :canEdit)
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('resourceId' => $accessGrantedResourceId, 'userId' => $nonAdminUserId, 'canEdit' => 0));
        $stmt->execute(array('resourceId' => $accessGrantedResourceId, 'userId' => $adminUserId, 'canEdit' => 1));
        $stmt->execute(array('resourceId' => $writeGrantedResourceId, 'userId' => $nonAdminUserId, 'canEdit' => 1));



        // ACT
        // Non-admin user access to unpublished resource without explicit whitelisting
        $nonAdminUnpubResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $unpublishedResourceId, $conn, false);

        // Non-admin user access to published resource
        $nonAdminPubResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $publishedResourceId, $conn, false);

        // Non-admin user access to unpublished resource where user is whitelisted
        $nonAdminWhitelistedResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $accessGrantedResourceId, $conn, false);

        // Admin user access to unpublished resource
        $adminUnpublishedResult = CheckResourceUserAccess::checkUserAccess($adminUserId, $unpublishedResourceId, $conn, false);

        // Admin user access to published resource
        $adminPublishedResult = CheckResourceUserAccess::checkUserAccess($adminUserId, $publishedResourceId, $conn, false);

        // Admin access to unpublished resource when whitelisted
        $adminWhitelistedResult = CheckResourceUserAccess::checkUserAccess($adminUserId, $unpublishedResourceId, $conn, false);

        // Access to unpublished resource ID with bogus user ID
        $bogusUserResult = CheckResourceUserAccess::checkUserAccess(666, $unpublishedResourceId, $conn, false);

        // Access to bogus resource ID
        $bogusPageResult = CheckResourceUserAccess::checkUserAccess($adminUserId, 999, $conn, false);

        // Non-admin write access to write-granted resource ID
        $writeAccessResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $writeGrantedResourceId, $conn, true);

        // Non-admin write access to published resource without permissions
        $nonAdminWriteResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $publishedResourceId, $conn, true);

        // Non-admin write access to unpublished resource without permissions
        $noPermissionWriteResult = CheckResourceUserAccess::checkUserAccess($nonAdminUserId, $unpublishedResourceId, $conn, true);

        // ASSERT
        // Non-admin user access to unpublished resource without explicit whitelisting
        $this->assertFalse($nonAdminUnpubResult);

        // Non-admin user access to published resource
        $this->assertTrue($nonAdminPubResult);

        // Non-admin user access to unpublished resource where user is whitelisted
        $this->assertTrue($nonAdminWhitelistedResult);

        // Admin user access to unpublished resource
        $this->assertTrue($adminUnpublishedResult);

        // Admin user access to published resource
        $this->assertTrue($adminPublishedResult);

        // Admin access to unpublished resource when whitelisted
        $this->assertTrue($adminWhitelistedResult);

        // Access to unpublished resource ID with bogus user ID
        $this->assertFalse($bogusUserResult);

        // Access to bogus resource ID
        $this->assertFalse($bogusPageResult);

        // Non-admin write access to write-granted resource ID
        $this->assertTrue($writeAccessResult);

        // Non-admin write access to published resource without permissions
        $this->assertFalse($nonAdminWriteResult);

        // Non-admin write access to unpublished resource without permissions
        $this->assertFalse($noPermissionWriteResult);

    }
}
