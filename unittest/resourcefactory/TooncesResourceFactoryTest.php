<?php
/**
 * @author paulanderson
 * initial Commit: 6/20/18
 */

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../toonces_library/php/toonces.php';
require_once __DIR__ . '../../TooncesTestCase.php;

class TooncesResourceFactoryTest extends TooncesTestCase
{

    public function testMakeResource() {
        // ARRANGE
        $this->destroyTestDatabase();
        $this->buildTestDatabase();

        $conn = $this->getConnection();
        $resourceFactory = new TooncesEndpointSystem();
        $resourceFactory->conn = $conn;
        $resourcePathname = 'test_resource';
        $resourceId = $this->createPage(true, 1, $resourcePathname);
        $resourceUri = GrabResourceURL::getURL($resourceId, $conn);

        // ACT
        $resource = $resourceFactory->makeResource($resourceUri);

        // ASSERT
        $this->assertInstanceOf('Resource', $resource);

        $this->destroyTestDatabase();
    }


    public function testResourceNotFound() {
        // ARRANGE
        $this->destroyTestDatabase();
        $this->buildTestDatabase();


        $conn = $this->getConnection();
        $resourceFactory = new TooncesEndpointSystem();
        $resourceFactory->conn = $conn;
        $resourceUri = 'bogus_resource_uri';

        $configXml = new DOMDocument();
        $configXml->load(ROOTPATH . 'toonces-config.xml');

        $resourceNameNode = $configXml->getElementsByTagName('resource_404_class')->item(0);
        $expectedClassName = $resourceNameNode->nodeValue;

        // ACT
        $resource = $resourceFactory->makeResource($resourceUri);

        // ASSERT
        $this->assertInstanceOf($expectedClassName, $resource);

        $this->destroyTestDatabase();
    }
}
