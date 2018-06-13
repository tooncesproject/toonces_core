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

class TooncesResourceFactory implements iResourceFactory {

    /**
     * @var PDO
     */
    private $conn;


    public function makeResource($paramResourceUri) {
        $this->conn = UniversalConnect::doConnect();
        $resourceId = $this->getResourceId($paramResourceUri);
        return $this->getResourceById($resourceId);

    }


    /**
     * @param string $paramPathString
     * @return int
     */
    private function getResourceId($paramPathString) {

        $defaultPage = 1;
        $depthCount = 0;

        // return home resource if no path string
        if (trim($paramPathString) == '') {
            return $defaultPage;
        } else {
            $pathArray = explode('/', $paramPathString);

            // recursively query pages tables until end is reached
            $resourceId = SearchPathString::grabResourceId($pathArray, $defaultPage, $depthCount, $this->conn);

            return $resourceId;
        }
    }


    /**
     * @param $paramResourceId
     * @return iResource
     */
    private function getResourceById($paramResourceId) {
        $sql = <<<SQL
        SELECT
            resource_class
        FROM
            resource
        WHERE
            resource_id = :resourceId
SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['resourceId' => $paramResourceId]);
        $result = $stmt->fetchAll();
        $resourceClassName = $result['resource_class'];

        $resource = $this->dynamicallyInstantiateResource($resourceClassName);
        $resource->setResourceId($paramResourceId);

        return $resource;

    }

    /**
     * @param string $paramResourceClassName
     * @return iResource
     */
    private function dynamicallyInstantiateResource($paramResourceClassName) {
        return new $paramResourceClassName;
    }

}
