<?php
/**
 * @author paulanderson
 * Date: 10/3/18
 * Time: 12:07 PM
 */

interface iAuthenticator
{

    /**
     * @param Request $paramRequest
     * @return int
     */
    public function authenticate($paramRequest);

}
