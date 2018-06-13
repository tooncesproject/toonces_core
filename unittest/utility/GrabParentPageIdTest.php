<?php
/**
 * @author paulanderson
 * GrabParentResourceIdTest.php
 * Initial commit: Paul Anderson, 4/2/2018
 *
 * Unit test for the static utility GrabParentResourceId
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class GrabParentResourceIdTest extends SqlDependentTestCase {

    function testGrabParentResourceId() {
        // ARRANGE
        $conn = $this->getConnection();
        $this->destroyTestDatabase();
        $this->buildTestDatabase();
        // Create a resource. the createUnpublishedPage method creates a resource with
        // a parent ID of 1.
        $parentResourceId = $this->createPage(true,1);
        $childResourceId = $this->createPage(true, $parentResourceId);

        // ACT
        $expectedResourceId = GrabParentResourceId::getParentId($childResourceId, $conn);

        // ASSERT
        $this->assertEquals($parentResourceId, $expectedResourceId);

        // Tear down
        $this->destroyTestDatabase();
    }
}
