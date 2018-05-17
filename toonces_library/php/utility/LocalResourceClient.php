<?php
/**
 * @author paulanderson
 * LocalResourceClient.php
 * Initial commit: Paul Anderson, 5/13/2018
 *
 * iResourceClient compliant class that stores, deletes and retrieved files locally instead of
 * at an HTTP resource.
 *
 */

require_once LIBPATH.'php/toonces.php';

// Dummy client for testing.
class LocalResourceClient implements iResourceClient {

    var $httpStatus;
    function getHttpStatus() {
        return $this->httpStatus;
    }

    function get($url, $username = null, $password = null, $paramHeaders = array()) {
        $content = file_get_contents($url);
        if ($content) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }
        return $content;

    }

    function put($url, $data, $username = null, $password = null, $headers = array()) {
        $success = file_put_contents($url, $data);
        if ($success) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }

        return $success;
    }

    function delete($url, $username = null, $password = null, $headers = array()) {
        $success = unlink($url);
        if ($success) {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse');
        } else {
            $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
        }
    }
}
