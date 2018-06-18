<?php
/**
 * @author paulanderson
 * ApiResourceTest.php
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Unit tests for the ApiResource abstract class
 *
 * */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';


class ConcreteApiResource extends ApiResource {
    // inherits all functionality
    public function getResource()
    {
        return parent::getResource();
    }
}

class ApiResourceTest extends SqlDependentTestCase {

    public function testAuthenticateUser() {
        // ARRANGE
        $ar = new ConcreteApiResource();
        // Set up SQL connection
        $sqlConn = $this->getConnection();
        $ar->conn = $sqlConn;

        // Set up Toonces database fixture
        $this->destroyTestDatabase();
        $this->buildTestDatabase();

        // ACT
        // Attempt login with no user
        if (array_key_exists('PHP_AUTH_USER', $_SERVER))
            unset($_SERVER['PHP_AUTH_USER']);

            if (array_key_exists('PHP_AUTH_PW', $_SERVER))
                unset($_SERVER['PHP_AUTH_PW']);

                $noLogin = $ar->authenticateUser();

                // Attempt authentication with bogus user
                $_SERVER['PHP_AUTH_USER'] = 'badguy@evil.com';
                $_SERVER['PHP_AUTH_PW'] = 'bogusPassword';

                $badLogin = $ar->authenticateUser();

                // Attempt with valid user
                $_SERVER['PHP_AUTH_USER'] = $GLOBALS['TOONCES_USERNAME'];
                $_SERVER['PHP_AUTH_PW'] = $GLOBALS['TOONCES_PASSWORD'];

                $goodLogin = $ar->authenticateUser();

                $this->destroyTestDatabase();

                // ASSERT
                $this->assertNull($noLogin);
                $this->assertNull($badLogin);
                $this->assertTrue(is_int(intval($goodLogin)));
    }

    /**
     * @expectedException Exception
     */
    public function testGetResourceException() {
        // ARRANGE
        // Instantiate base objects
        $ar = new ConcreteApiResource();
        $testObjectArray = array('testObject' => 'foo');
        $ar->dataObjects = $testObjectArray;

        // Inject HTTP host
        $_SERVER['HTTP_HOST'] = 'example.com';

        // Inject Resource URI
        $ar->resourceUri = 'path';

        // ACT
        // Try it with no HTTP verb
        if (isset($_SERVER['REQUEST_METHOD'])) {
            unset($_SERVER['REQUEST_METHOD']);
        }

        // Expect Exception here
        $ar->getResource();

    }


    public function testGetResource() {
        // This test also covers the "action" methods of the abstract class.

        // ARRANGE
        // Instantiate base objects
        $ar = new ConcreteApiResource();
        $testObjectArray = array('testObject' => 'foo');
        $ar->resourceData = $testObjectArray;

        // Inject HTTP host
        $_SERVER['HTTP_HOST'] = 'example.com';

        // Inject Resource URI
        $ar->resourceUri = 'path';

        // ACT
        // Call the method with each "supported" HTTP verb.
        // Also, we include the required content-type header.

        // GET
        $ar->resourceData = $testObjectArray;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getResult = $ar->getResource();
        $getStatus = $ar->httpStatus;
        $httpURL = $ar->resourceUrl;

        // POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTPS'] = 'on';
        $postResult = $ar->getResource();
        $postStatus = $ar->httpStatus;
        $httpsURL = $ar->resourceUrl;

        // HEAD
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $ar->getResource();
        $headStatus = $ar->httpStatus;

        // PUT
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $ar->getResource();
        $putStatus = $ar->httpStatus;

        // OPTIONS
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $ar->getResource();
        $optionsStatus = $ar->httpStatus;

        // DELETE
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $ar->getResource();
        $deleteStatus = $ar->httpStatus;

        // CONNECT
        $_SERVER['REQUEST_METHOD'] = 'CONNECT';
        $ar->getResource();
        $connectStatus = $ar->httpStatus;


        // ASSERT
        // GET
        // Only one assertion for $testObjectArray - We just wanna know it will return something.
        $this->assertSame($testObjectArray, $getResult);
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $getStatus);
        // One assertion for 'http' URL scheme - We won't repeat this.
        $this->assertSame($httpURL, 'http://example.com/path');

        // POST
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $postStatus);
        // One assertion for 'https' URL scheme - We won't repeat this.
        $this->assertSame($httpsURL, 'https://example.com/path');

        // HEAD
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $headStatus);

        // PUT
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $putStatus);

        // OPTIONS
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $optionsStatus);

        // DELETE
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $deleteStatus);

        // CONNECT
        $this->assertEquals(Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse'), $connectStatus);
    }

}
