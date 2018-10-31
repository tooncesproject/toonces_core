<?php
/**
 * @author paulanderson
 * Date: 10/30/18
 * Time: 10:45 PM
 */

class StaticEndpointOperatorFactory
{

    /**
     * @return iEndpointOperator
     */
    public static function makeEndpointOperator()
    {
        // Get config parameters
        $parameters = parse_ini_file(LIBPATH . 'settings/toonces.ini');
        $builderClass = $parameters['endpointOperatorBuilderClass'];
        $endpointOperatorBuilder = StaticEndpointOperatorFactory::makeEndpointOperatorBuilder($builderClass);

        return $endpointOperatorBuilder->makeEndpointOperator();
    }

    /**
     * @param string $endpointOperatorBuilderClass
     * @return iEndpointOperatorBuilder
     */
    private static function makeEndpointOperatorBuilder($endpointOperatorBuilderClass)
    {
        // Get config parameters
        return new $endpointOperatorBuilderClass;
    }
}
