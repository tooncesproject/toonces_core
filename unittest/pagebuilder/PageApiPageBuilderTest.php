<?php
/**
 * @author paulanderson
 * PageApiPageBuilderTest.php
 * Initial commit: 5/5/2018
 *
 * Unit tests for the PageApiPageBuilder class
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class PageApiPageBuilderTest extends TestCase {

    function testBuildPage() {
        // ARRANGE
        $pageView = new FilePageview(1);
        $builder = new PageApiPageBuilder($pageView);

        // ACT
        $array = $builder->buildPage();
        $resource = $array[0];

        // ASSERT
        // Builder creates resource
        $this->assertInstanceOf(PageDataResource::class, $resource);

    }
}
