<?php
/**
 * @author paulanderson
 * iResourceClient.php
 * Initial commit: Paul Anderson, 5/5/2018
 *
 * Interface for classes performing authenticated HTTP operations.
 *
 */

include_once LIBPATH.'php/toonces.php';

interface iResourceClient {

    function getHttpStatus();
    function get($url, $username = null, $password = null, $paramHeaders = array());
    function put($url, $data, $username = null, $password = null, $paramHeaders = array());
    function delete($url, $username = null, $password = null, $paramHeaders = array());

}
