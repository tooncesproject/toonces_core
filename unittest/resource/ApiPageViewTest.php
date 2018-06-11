<?php
/**
 * @author paulanderson
 *
 * ApiPageViewTest.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Unit tests for the ApiPageView class
 *
*/

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';


// Concrete class for testing
class ConcreteApiPageView extends ApiPageView {
    // No additional functionality.
}

class ApiPageViewTest extends SqlDependentTestCase {

    // We will omit unit tests for simple setter/getter methods.
    // I'm not THAT pedantic. --Paul

    function testConstruct() {
        // ARRANGE
        $pageId = 1;
        $version = '2.0';
        $_SERVER['HTTP_ACCEPT_VERSION'] = $version;

        //ACT
        $apv = new ConcreteApiPageView($pageId);

        // ASSERT
        $this->assertEquals($pageId, $apv->pageId);
        $this->assertSame($version, $apv->apiVersion);
    }


    /**
     * @expectedException Exception
     */
    function testGetResource() {
        // ARRANGE
        $apv = new ConcreteApiPageView(1);

        $tooManyDataObjects = array('oneObject' => 'foo', 'oneObjectTooMany' => 'bar');
        $justOneDataObject = array('oneObject' => 'foo');

        // ACT
        // The object should throw an error if we attempt to give it an array longer than 1.
        $failed = false;
        $apv->dataObjects = $tooManyDataObjects;
        try {
            $apv->getResource();
        } finally {
            $failed = true;
        }

        // With correct setup, it should just return the same array as set.
        $apv->dataObjects = $justOneDataObject;
        $output = $apv->getResource();

        // ASSERT
        $this->assertTrue($failed);
        $this->assertSame($justOneDataObject, $output);

    }


}
