<?php
/**
 * @author paulanderson
 * TestPageDataResource.php
 * Initial commit: Paul Anderson, 5/2/2018
 * 
 * Unit tests for the PageApiResource class.
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestPageDataResource extends SqlDependentTestCase {
    
    function testPostAction() {
        // ARRANGE
        // get SQL connection
        $conn = $this->getConnection();
        // Set up database fixture
        $this->buildTestDatabase();
        
        
        // ACT
        // ASSERT
        // POST with no authentication returns 400 error
        // POST with bogus authentication returns 400 error
        // POST with parent page to which non-admin user doesn't have access returns 400
        // POST with non-existent parent page ID returns 404
        // POST with admin user returns 200, creates record in database
        
    }
   

    function testPutAction() {
        // ARRANGE
        // ACT
        // ASSERT
    }
    
    
    function testGetAction() {
        // ARRANGE
        // ACT
        // ASSERT
    }
    
    
    function testDeleteAction() {
        // ARRANGE
        // ACT
        // ASSERT
    }

}