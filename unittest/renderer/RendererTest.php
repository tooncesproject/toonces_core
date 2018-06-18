<?php
/**
 * @author paulanderson
 *
 * RendererTest.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Unit tests for the ApiRenderer class
 *
*/

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';


// Concrete classes for testing
class ConcreteRenderer extends Renderer {
    public function renderResource($resource) {
        parent::renderResource($resource);
    }
}

class RendererTestResource extends Resource {

    public function getResource() {
        return 'foo';
    }

    public function render() {
        // implementation not required for this test
    }
}



class RendererTest extends TestCase {

    /**
     * @expectedException Exception
     */
    public function testSendHttpStatusHeaderThrowsException() {
        // ARRANGE
        $renderer = new ConcreteRenderer();
        $resource = new RendererTestResource();


        // ACT
        // Resource will not have http status set;
        // expectation is this will throw an exception.
        $renderer->sendHttpStatusHeader($resource);

        // ASSERT
        // (assertion is exception thrown per PHPDoc @expectedException)
   }


}
