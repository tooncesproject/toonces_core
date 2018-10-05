<?php
/**
 * @author paulanderson
 * Date: 10/4/18
 * Time: 5:03 PM
 */

require_once LIBPATH . '/php/toonces.php';

class XmlResourceFactory implements iResourceFactory
{

    public function makeResource($resourceUri)
    {
        $xml = new DOMDocument();
        $xml->load(ROOTPATH.'pages.xml');
        $xml->validate();
        $resourceId = $this->getResourceId($resourceUri);
        if (!is_null($resourceId)) {
            $resourceElement = $xml->getElementById($resourceId);
            $resourceClass = $resourceElement->getAttribute('resource_class');

        } else {
            $resourceClass = $this->getDefault404ResourceClassName();
        }
        $resource = $this->dynamicallyInstantiateResource($resourceClass);

        // $resource->setResourceUri($resourceUri);
        return $resource;

    }

    /**
     * @param string $pathString
     * @return string
     */
    private function getResourceId($pathString)
    {

        $defaultPage = '0';
        $depthCount = 0;

        // return home resource if no path string
        if (trim($pathString) == '') {
            return $defaultPage;
        } else {
            $pathArray = explode('/', $pathString);

            // recursively query pages tables until end is reached
            $resourceId = $this->searchPagesXml($pathArray, $defaultPage, $depthCount);

            return $resourceId;
        }
    }

    /**
     * @param array $pathArray
     * @param string $defaultPage
     * @param int $depthCount
     * @return int|null
     */
    private function searchPagesXml($pathArray, $defaultPage, $depthCount)
    {

        $pageFound = false;
        $descendantResourceId = null;

        $xml = new DOMDocument();
        $xml->load(ROOTPATH.'pages.xml');

        // Get element by ID
        $startingElement = $xml->getElementById($defaultPage);

        // Get pathnames of descendant pages
        foreach ($startingElement->childNodes as $childNode) {
            if ($childNode->getAttribute('pathname') == $pathArray[$depthCount]) {
                $descendantResourceId = $childNode->getAttribute('id');
                $pageFound = true;
                break;
            }
        }

        // if a page was found and the end of the array has been reached, return the descendant ID
        // otherwise continue recursion
        $nextDepthCount = ++$depthCount;

        if ($pageFound && (!array_key_exists($nextDepthCount, $pathArray) OR trim($pathArray[$nextDepthCount]) == '')) {
            return $descendantResourceId;

        } else if ($pageFound) {
            // iterate recursion if page found
            return $this->searchPagesXml($pathArray, $descendantResourceId, $nextDepthCount);

        } else {
            return Null;
        }

    }

    /**
     * @param string $resourceClassName
     * @return Resource
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

