<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 5:22 PM
 */

interface iEndpointSystemFactory
{
    /** @return iEndpointSystem */
    public function makeEndpointSystem();
}
