<?php
/**
 * @author paulanderson
 * TestGrabParentPageId.php
 * Initial commit: Paul Anderson, 4/2/2018
 * 
 * Unit test for the static utility GrabParentPageId
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestGrabParentPageId extends SqlDependentTestCase {
    
    function testGrabParentPageId() {
        // ARRANGE
        $conn = $this->getConnection();
        $this->buildTestDatabase();
        // Create a page. the createUnpublishedPage method creates a page with
        // a parent ID of 1.
        $pageId = $this-> createPage(false);

        // ACT
        $parentPageId = GrabParentPageId::getParentId($pageId, $conn);

        // ASSERT
        $this->assertEqual(1, $parentPageId);
        
        // Tear down
        $this->destroyTestDatabase();
    }
}