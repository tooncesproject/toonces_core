<?php
/**
 * @author paulanderson
 * TestCheckUserAccess.php
 * Initial Commit: Paul Anderson, 4/27/2018
 * 
 * Unit test for the CheckUserAccess static utility.
 * 
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestCheckUserAccess extends SqlDependentTestCase {

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
        $unpublishedPageId = $this->createUnpublishedPage();
        $publishedPageId = $this->createPublishedPage();
        $accessGrantedPageId = $this->createUnpublishedPage();
        $sql = <<<SQL
        INSERT INTO page_user_access
            (page_id, user_id, can_edit)
        VALUES
            (:pageId, :userId, TRUE)
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('pageId' => $accessGrantedPageId, 'userId' => $nonAdminUserId));
        $stmt->execute(array('pageId' => $accessGrantedPageId, 'userId' => $adminUserId));
        
        // ACT
        // Non-admin user access to unpublished page without explicit whitelisting
        $nonAdminUnpubResult = CheckPageUserAccess::checkUserAccess($nonAdminUserId, $unpublishedPageId, $conn);

        // Non-admin user access to published page
        $nonAdminPubResult = CheckPageUserAccess::checkUserAccess($nonAdminUserId, $publishedPageId, $conn);

        // Non-admin user access to unpublished page where user is whitelisted
        $nonAdminWhitelistedResult = CheckPageUserAccess::checkUserAccess($nonAdminUserId, $unpublishedPageId, $conn);
        
        // Admin user access to unpublished page
        $adminUnpublishedResult = CheckPageUserAccess::checkUserAccess($adminUserId, $unpublishedPageId, $conn);
        
        // Admin user access to published page
        $adminPublishedResult = CheckPageUserAccess::checkUserAccess($adminUserId, $publishedPageId, $conn);
        
        // Admin access to unpublished page when whitelisted
        $adminWhitelistedResult = CheckPageUserAccess::checkUserAccess($adminUserId, $unpublishedPageId, $conn);

        // Access to unpublished page ID with bogus user ID
        $bogusUserResult = CheckPageUserAccess::checkUserAccess(666, $unpublishedPageId, $conn);

        // Access to bogus page ID
        $bogusPageResult = CheckPageUserAccess::checkUserAccess($adminUserId, 999, $conn);

        // ASSERT
        // Non-admin user access to unpublished page without explicit whitelisting
        $this->assertFalse($nonAdminUnpubResult);

        // Non-admin user access to published page
        $this->assertTrue($nonAdminPubResult);
        
        // Non-admin user access to unpublished page where user is whitelisted
        $this->assertTrue($nonAdminWhitelistedResult);

        // Admin user access to unpublished page
        $this->assertTrue($adminUnpublishedResult);

        // Admin user access to published page
        $this->assertTrue($adminPublishedResult);

        // Admin access to unpublished page when whitelisted
        $this->assertTrue($adminWhitelistedResult);

        // Access to unpublished page ID with bogus user ID
        $this->assertFalse($bogusUserResult);
        
        // Access to bogus page ID
        $this->assertFalse($bogusPageResult);
        
    }
}