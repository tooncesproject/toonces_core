<?php
/**
 * @author paulanderson
 * Initial commit: 6/20/18
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class DomDocumentRendererTest extends TestCase {

    /**
     * @throws Exception
     * @runInSeparateProcess
     */
    public function testRenderResource() {
        // ARRANGE
        $_SERVER['HTTP_HOST'] = 'example.com';
        $resource = new TooncesWelcomeDomDocumentResource();
        $resource->httpMethod = 'GET';
        $resource->htmlResourcePath = LIBPATH . 'html/';
        $resourceDomDocument = $resource->getResource();
        $expectedOutput = $resourceDomDocument->saveHTML();
        $renderer = new DomDocumentRenderer();

        // ACT
        $renderer->renderResource($resource);
        $output = ob_get_contents();

        // ASSERT
        $this->assertSame($expectedOutput, $output);

    }
}
