<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 5:22 PM
 */

interface iEndpointOperatorBuilder
{
    /** @return iEndpointOperator */
    public function makeEndpointOperator();
}
