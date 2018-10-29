<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 5:24 PM
 */

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class LocalXmlEndpointOperatorBuilder implements iEndpointOperatorBuilder
{
    public function makeEndpointSystem()
    {
        $settings = parse_ini_file(__DIR__ . '/settings/XmlEndpointOperator.ini');
        $endpointSystemRootPath = $settings['endpointSystemRootPath'];

        if (empty($endpointSystemRootPath))
            $endpointSystemRootPath = __DIR__;

        // TODO try/except block here ?
        $filesystemAdapter = new Local($endpointSystemRootPath);
        $filesystem = new Filesystem($filesystemAdapter);

        $endpointSystem = new XmlEndpointOperator($filesystem);

        $endpointSystem->endpointXmlFilePath = $settings['endpointXmlFilePath'];

        return $endpointSystem;
    }
}
