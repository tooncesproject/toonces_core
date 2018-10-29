<?php
/**
 * @author paulanderson
 * Date: 10/4/18
 * Time: 5:03 PM
 */

require_once LIBPATH . '/php/toonces.php';
use League\Flysystem\FileNotFoundException;


class XmlEndpointOperator implements iEndpointOperator
{

    /** @var DOMDocument */
    public $domDocument;

    /** @var \League\Flysystem\Filesystem */
    public $filesystem;

    /** @var string */
    public $endpointXmlFilePath;

    /**
     * XmlEndpointOperator constructor.
     * @param \League\Flysystem\Filesystem $filesystem
     * @throws XmlReadWriteException
     */
    public function __construct($filesystem)
    {
        $this->filesystem = $filesystem;
        if (!$this->domDocument)
            $this->loadXml();
    }


    /**
     * @param int $parentEndpointId
     * @param string $title
     * @param string $pathName
     * @param string $resourceClassName
     * @return Endpoint
     * @throws XmlReadWriteException
     * @throws XmlCreateEndpointException
     */
    public function createEndpoint($parentEndpointId, $title, $pathName, $resourceClassName)
    {
        $errorMessage = '';
        do {
            if (!$this->readEndpointById($parentEndpointId)) {
                $errorMessage = 'Create endpoint failed; parent endpoint ID ' . strval($parentEndpointId)
                    . ' does not exist.';
                break;
            }
            if (!$this->validatePathName($pathName)) {
                $errorMessage = "Pathname " . $pathName
                    . " invalid; pathnames may only containing letters, numbers, _ or - ";
                break;
            }
            if ($this->checkPathnameExists($pathName, $parentEndpointId)) {
                $errorMessage = "Parent endpoint with ID "
                    . strval($parentEndpointId)
                    . " already has a child with pathname " . $pathName . ".";
                break;
            }

        } while (false);
        if (!empty($errorMessage)) {
            throw new XmlCreateEndpointException($errorMessage);
        }

        $endpoint = new Endpoint();
        $endpoint->endpointId = $this->getMaxEndpointId() + 1;
        $endpoint->title = $title;
        $endpoint->pathname = $pathName;
        $endpoint->resourceClassName = $resourceClassName;
        $this->insertEndpoint($parentEndpointId, $endpoint);
        $this->writeXml();
        return $endpoint;

    }

    /**
     * @param string $endpointUri
     * @return Endpoint
     * @throws EndpointNotFoundException
     * @throws XmlReadWriteException
     */
    public function readEndpointByUri($endpointUri) {
        $pathArray = array_reverse(explode('/', $endpointUri));
        $endpointId = $this->recursivelyFindEndpoint($pathArray, 0);
        if (sizeof($pathArray) == 0 && $endpointId == 0)
            throw new EndpointNotFoundException('Endpoint not found at URI ' . $endpointUri);

        return $this->readEndpointById($endpointId);

    }

    /**
     * @param int $endpointId
     * @param bool $recursive
     * @return Endpoint
     * @throws XmlReadWriteException
     */
    public function readEndpointById($endpointId, $recursive = false)
    {

        $endpointIdStr = 'id_' . strval($endpointId);
        $endpointElement = $this->domDocument->getElementById($endpointIdStr);
        $endpoint = new Endpoint();
        $endpoint->endpointId = $endpointId;
        $endpoint->title = $endpointElement->getAttribute('title');
        $endpoint->pathname = $endpointElement->getAttribute('pathname');
        $endpoint->resourceClassName = $endpointElement->getAttribute('resourceClassName');

        if ($recursive) {
            foreach ($endpointElement->childNodes as $childNode) {
                if (get_class($childNode) == 'DOMElement') {
                    $childEndpointId = intval($childNode->getAttribute('xml:id'));

                    if (!$endpoint->children)
                        $endpoint->children = array();

                    array_push($endpoint->children, $this->readEndpointById($childEndpointId));
                }
            }
        }

        return $endpoint;
    }

    /**
     * @param Endpoint $endpoint
     * @return Endpoint
     * @throws XmlReadWriteException
     */
    public function updateEndpoint($endpoint)
    {
        $endpointIdStr = 'id_' . strval($endpoint->endpointId);
        $endpointElement = $this->domDocument->getElementById($endpointIdStr);
        $endpointElement->setAttribute('title', $endpoint->title);
        $endpointElement->setAttribute('pathname', $endpoint->pathname);
        $endpointElement->setAttribute('resourceClassName', $endpoint->resourceClassName);
        $this->writeXml();
        return $endpoint;
    }

    /**
     * @param int $endpointId
     * @throws XmlReadWriteException
     * @return Endpoint
     */
    public function deleteEndpoint($endpointId)
    {
        $endpointIdStr = 'id_' . strval($endpointId);
        $endpointElement = $this->domDocument->getElementById($endpointIdStr);

        $deletedEndpoint = $this->readEndpointById($endpointId, true);

        $endpointElement->parentNode->removeChild($endpointElement);
        $this->writeXml();

        return $deletedEndpoint;

    }

    /**
     * @throws XmlReadWriteException
     */
    private function loadXml()
    {
        $settings = parse_ini_file(LIBPATH . 'settings/XmlEndpointOperator.ini');
        $endpointXmlFilePath = $settings['endpointXmlFilePath'];
        $this->domDocument = new DOMDocument();
        try {
            $xml = $this->filesystem->read($endpointXmlFilePath);
        } catch (FileNotFoundException $e) {
            throw new XmlReadWriteException('Failed to read endpoints XML file: ' . $e->getMessage());
        }

        $this->domDocument->loadXML($xml);

    }

    /**
     * @throws XmlReadWriteException
     */
    private function writeXml()
    {
        $settings = parse_ini_file(LIBPATH . 'settings/XmlEndpointOperator.ini');
        $endpointXmlFilePath = $settings['endpointXmlFilePath'];
        try {
            $this->filesystem->put($endpointXmlFilePath, $this->domDocument->saveXML());
        } catch (Exception $e) {
            throw new XmlReadWriteException('Failed to write endpoints XML file: ' . $e->getMessage());
        }
    }

    /**
     * @param string $pathName
     * @return bool
     */
    private function validatePathName($pathName)
    {
        // Pathname contains disallowed characters, or is empty?
        if (!ctype_alnum(preg_replace('[_|-]', '', $pathName)) | empty($pathName)) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * @param $pathname
     * @param $parentEndpointId
     * @return bool
     * @throws XmlReadWriteException
     */
    private function checkPathnameExists($pathname, $parentEndpointId)
    {
        $pathnameExists = false;
        $parentEndpoint = $this->readEndpointById($parentEndpointId, true);
        foreach ($parentEndpoint->children as $childEndpoint) {
            if ($pathname == $childEndpoint->pathname) {
                $pathnameExists = true;
                break;
            }
        }

        return $pathnameExists;
    }

    /**
     * @param int $parentEndpointId
     * @param Endpoint $endpoint
     * @throws XmlReadWriteException
     */
    private function insertEndpoint($parentEndpointId, $endpoint)
    {
        // $endpointElement = new DOMElement('page');
        $endpointElement = $this->domDocument->createElement('endpoint');
        $endpointElement->setAttribute('xml:id', 'id_' . strval($endpoint->endpointId));
        $endpointElement->setAttribute('title', $endpoint->title);
        $endpointElement->setAttribute('pathname', $endpoint->pathname);
        $endpointElement->setAttribute('resourceClassName', $endpoint->resourceClassName);

        $endpointIdStr = 'id_' . strval($parentEndpointId);
        $parentElement = $this->domDocument->getElementById($endpointIdStr);
        $parentElement->appendChild($endpointElement);
        //$this->domDocument->validate();

    }


    /**
     * @throws XmlReadWriteException
     * @return int
     */
    private function getMaxEndpointId($startingId = 0)
    {

        $endpointIdStr = 'id_' . strval($startingId);
        $startingEndpointElement = $this->domDocument->getElementById($endpointIdStr);
        if ($startingEndpointElement->hasChildNodes()) {
            foreach ($startingEndpointElement->childNodes as $childNode)
                if (get_class($childNode) == 'DOMElement') {
                    $nodeIdAttribute = $childNode->getAttribute('xml:id');
                    $nodeId = intval(str_replace('id_', '', $nodeIdAttribute));
                    $startingId = max([$startingId, $this->getMaxEndpointId($nodeId)]);
                }
        }
        return $startingId;
    }


    /**
     * @param $pathArray
     * @param $startingEndpointId
     * @return mixed
     * @throws XmlReadWriteException
     */
    private function recursivelyFindEndpoint($pathArray, $startingEndpointId)
    {
        $endpointIdStr = 'id_' . strval($startingEndpointId);
        $endpointElement = $this->domDocument->getElementById($endpointIdStr);
        $targetPathname = array_pop($pathArray);
        foreach ($endpointElement->childNodes as $childNode) {
            if (get_class($childNode) == 'DOMElement') {
                if ($childNode->getAttribute('pathname') == $targetPathname)
                    return $this->recursivelyFindEndpoint($pathArray, $childNode->getAttribue('xml:id'));
            }
        }

        return $startingEndpointId;
    }
}
