<?php
/**
 * @author paulanderson
 *
 * ExtHtmlPageBuilderTest.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Unit test the ExtHtmlPageBuilder class
 *
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class ExtHtmlPageBuilderTest extends SqlDependentTestCase{

    /**
     * @runInSeparateProcess
     */
    function testCreateContentElement() {
        // ARRANGE
        $conn = $this->getConnection();
        $htmlPageView = new HTMLPageView(1);
        $htmlPageView->setSQLConn($conn);
        $this->buildTestDatabase();
        $ehpb = new ExtHTMLPageBuilder($htmlPageView);

        // ACT
        $ehpb->createContentElement();

        // ASSERT
        $this->assertInstanceOf(ExtHtmlResource::class, $ehpb->contentElement);

        // TEARDOWN
        $this->destroyTestDatabase();
    }
}
