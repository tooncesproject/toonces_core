<?php
/**
 * @author paulanderson
 * TestDocumentEndpointPageBuilder.php
 * Initial commit: 4/29/2018
 *
 * Unit tests for the DocumentEndpointPageBuilder class
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class TestDocumentEndpointPageBuilder extends TestCase {

    function testBuildPage() {
        // ARRANGE
        $pageView = new FilePageview(1);
        $builder = new DocumentEndpointPageBuilder($pageView);

        // ACT
        $array = $builder->buildPage();
        $resource = $array[0];

        // ASSERT
        // Builder creates resource
        $this->assertInstanceOf(FileResource::class, $resource);

    }
}
