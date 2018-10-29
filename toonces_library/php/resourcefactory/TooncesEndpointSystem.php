<?php
/**
 * @author paulanderson
 * TooncesResourceFactory.php
 * Initial commit: Paul Anderson, 6/12/18
 *
 * Default ResourceFactory class for Toonces.
 *
 */

require_once LIBPATH . '/php/toonces.php';

class TooncesEndpointSystem implements iEndpointSystem {

    /**
     * @var PDO
     */
    public $conn;


    public function makeResource($endpointUri) {
        if (!$this->conn)
            $this->conn = UniversalConnect::doConnect();

        $resourceId = $this->getResourceId($endpointUri);
        $resource = $this->getResourceById($resourceId);
        $resource->setResourceUri($endpointUri);
        return $resource;

    }


    /**
     * @param string $pathString
     * @return int
     */
    private function getResourceId($pathString) {

        $defaultPage = 1;
        $depthCount = 0;

        // return home resource if no path string
        if (trim($pathString) == '') {
            return $defaultPage;
        } else {
            $pathArray = explode('/', $pathString);

            // recursively query pages tables until end is reached
            $resourceId = SearchPathString::grabResourceId($pathArray, $defaultPage, $depthCount, $this->conn);

            return $resourceId;
        }
    }


    /**
     * @param $resourceId
     * @return iResource
     */
    private function getResourceById($resourceId) {
        $sql = <<<SQL
        SELECT
            resource_class
        FROM
            resource
        WHERE
            resource_id = :resourceId
SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['resourceId' => $resourceId]);
        $result = $stmt->fetchAll();

        if ($result) {
            $resourceClassName = $result['resource_class'];
        } else {
            $resourceClassName = $this->getDefault404ResourceClassName();
        }

        $resource = $this->dynamicallyInstantiateResource($resourceClassName);
        $resource->setResourceId($resourceId);

        return $resource;

    }

    /**
     * @param string $resourceClassName
     * @return iResource
     */
    private function dynamicallyInstantiateResource($resourceClassName) {
        return new $resourceClassName;
    }

    private function getDefault404ResourceClassName() {
        $configXml = new DOMDocument();
        $configXml->load(ROOTPATH . 'toonces-config.xml');

        $resourceNameNode = $configXml->getElementsByTagName('resource_404_class')->item(0);
        $className = $resourceNameNode->nodeValue;
        return $className;
    }

}
