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

class TestGrabParentPageIdTest extends SqlDependentTestCase {
    
    function testGrabParentPageId() {
        // ARRANGE
        $conn = $this->getConnection();
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        // Create a page. the createUnpublishedPage method creates a page with
        // a parent ID of 1.
        $parentPageId = $this->createPage(true,1);
        $childPageId = $this->createPage(true, $parentPageId);

        // ACT
        $expectedPageId = GrabParentPageId::getParentId($childPageId, $conn);
        
        // ASSERT
        $this->assertEquals($parentPageId, $expectedPageId);

        // Tear down
        $this->destroyTestDatabase();
    }
}