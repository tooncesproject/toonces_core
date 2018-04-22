<?php
/*
* TestBlogDataResource.php
* Initial commit: Paul Anderson, 4/17/2018
* 
* Unit tests for the BlogDataResource class
*  
*/

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class TestBlogDataResource extends SqlDependentTestCase {
    
    function testConstruct() {
        // Test constructor method of BlogDataResource class
        // ARRANGE
        // Instantiate a PageView object
        $apiPageView = new APIPageView(1);
        
        // ACT
        // Instantiate a BlogDataResource object
        $bdr = new BlogDataResource($apiPageView);
        // ASSERT
        $this->assertIsInstance(BlogDataResource::class, $bdr);
    }


    function testBuildFields() {
        // Test the BuildFields method of BlogDataResource
        // Expected outcome: BDR holds an array of FieldValidator objects
        // ARRANGE
        $apiPageView = new APIPageView(1);
        $bdr = new BlogDataResource($apiPageView);
        
        // ACT
        $bdr->buildFields();
        
        // ASSERT
        foreach ($bdr->fields as $field) {
            $this->assertTrue(is_subclass_of($field, FieldValidator::class));
        }
    }

    
    
}