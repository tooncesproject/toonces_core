<?php
/**
 * @author paulanderson
 * Date: 10/5/18
 * Time: 10:54 AM
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../FileDependentTestCase.php';
require_once __DIR__ . '/../../toonces_library/php/toonces.php';

class XmlEndpointOperatorTest extends FileDependentTestCase
{

    public function setUp()
    {
        parent::setUp();

        $xmlFixture = <<<XML
        <endpoints>
            <endpoint title="Root endpoint" pathname="" xml:id="id_0" resourceClass="SomeResourceClass1">
                    <endpoint title="endpoint at depth 1" pathname="p_d_1" xml:id="id_1" resourceClass="SomeResourceClass2">
                        <endpoint title="endpoint at depth 2" pathname="p_d_2" xml:id="id_3" resourceClass="SomeResourceClass3">
                            <endpoint title="endpoint at depth 3" pathname="p_d_3" xml:id="id_4" resourceClass="SomeResourceClass4">
                        </endpoint>
                    </endpoint>
               </endpoint>
            </endpoint>
            <endpoint title="Second endpoint at depth 1" pathname="2_p_d_1" xml:id="id_2" resource_class="SomeResourceClass5" />
        </endpoints>
XML;

        $this->filesystem->put('endpoints.xml', $xmlFixture);

    }

    /**
     * @param Endpoint $endpoint
     * @throws \League\Flysystem\FileNotFoundException
     * @return bool
     */
    private function checkEndpointMatchesFixture($endpoint)
    {

        $domDocument = new DOMDocument();
        $xml = $this->filesystem->read('endpoints.xml');
        $domDocument->loadXML($xml);
        $endpointIdStr = 'id_' . strval($endpoint->endpointId);
        $endpointElement = $domDocument->getElementById($endpointIdStr);

        $endpointMatchesFixture =
            ($endpointElement->getAttribute('title') == $endpoint->title)
            &&
            ($endpointElement->getAttribute('pathname') == $endpoint->pathname)
            &&
            ($endpointElement->getAttribute('resourceClassName') == $endpoint->resourceClassName)
        ;

        return $endpointMatchesFixture;
    }


    /**
     * @throws CreateEndpointException
     * @throws EndpointReadWriteException]
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function testCreateEndpoint()
    {
        // ARRANGE
        $xmlEndpointSystem = new XmlEndpointOperator($this->filesystem);
        $parentEndpointId = 0;
        $title = 'test';
        $pathname = 'test_path';
        $resourceClassName = 'ResourceClassName';

        // ACT
        $newEndpoint = $xmlEndpointSystem->createEndpoint($parentEndpointId, $title, $pathname, $resourceClassName);

        // ASSERT
        $this->assertTrue($this->checkEndpointMatchesFixture($newEndpoint));

    }

    /**
     * @throws EndpointNotFoundException
     * @throws EndpointReadWriteException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function testReadEndpointByUri()
    {
        // ARRANGE
        $xmlEndpointSystem = new XmlEndpointOperator($this->filesystem);

        $uri = '/p_d_1/p_d_2/p_d_3/';

        // ACT
        $endpoint = $xmlEndpointSystem->readEndpointByUri($uri);

        // ASSERT
        $this->assertTrue($this->checkEndpointMatchesFixture($endpoint));
    }

    /**
     * @throws EndpointReadWriteException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function testReadEndpointById()
    {
        // ARRANGE
        $xmlEndpointSystem = new XmlEndpointOperator($this->filesystem);

        // ACT
        $endpoint = $xmlEndpointSystem->readEndpointById(3);

        // ASSERT
        $this->assertTrue($this->checkEndpointMatchesFixture($endpoint));
    }

    /**
     * @throws EndpointReadWriteException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function testUpdateEndpoint()
    {
        // ARRANGE
        $xmlEndpointSystem = new XmlEndpointOperator($this->filesystem);

        $updateEndpoint = new Endpoint();
        $updateEndpoint->endpointId = 3;
        $updateEndpoint->title = 'Updated Endpoint';
        $updateEndpoint->pathname = 'updated_endpoint';
        $updateEndpoint->resourceClassName = "UpdatedResourceClassName";

        // ACT
        $xmlEndpointSystem->updateEndpoint($updateEndpoint);

        // ASSERT
        $this->assertTrue($this->checkEndpointMatchesFixture($updateEndpoint));
    }

    /**
     * @throws EndpointReadWriteException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function testDeleteEndpoint()
    {
        // ARRANGE
        $xmlEndpointSystem = new XmlEndpointOperator($this->filesystem);

        // ACT
        $deletedEndpoint = $xmlEndpointSystem->deleteEndpoint(3);

        $domDocument = new DOMDocument();
        $xml = $this->filesystem->read('endpoints.xml');
        $domDocument->loadXML($xml);

        $endpointIdStr = 'id_' . strval($deletedEndpoint->endpointId);
        $endpointElement = $domDocument->getElementById($endpointIdStr);

        // ASSERT
        $this->assertEmpty($endpointElement);
        $this->assertNotEmpty($deletedEndpoint);

    }

}
