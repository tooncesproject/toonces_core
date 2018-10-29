<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 5:24 PM
 */

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class LocalXmlEndpointSystemBuilder implements iEndpointSystemFactory
{
    public function makeEndpointSystem()
    {
        $settings = parse_ini_file(__DIR__ . '/settings/XmlEndpointSystem.ini');
        $endpointSystemRootPath = $settings['endpointSystemRootPath'];

        if (empty($endpointSystemRootPath))
            $endpointSystemRootPath = __DIR__;

        // TODO try/except block here ?
        $filesystemAdapter = new Local($endpointSystemRootPath);
        $filesystem = new Filesystem($filesystemAdapter);

        $endpointSystem = new XmlEndpointSystem();
        $endpointSystem->filesystem = $filesystem;

        $endpointSystem->endpointXmlFilePath = $settings['endpointXmlFilePath'];

        return $endpointSystem;
    }
}
