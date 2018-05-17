<?php
/**
 * @author paulanderson
 * ResourceClient.php
 * Initial commit: Paul Anderson, 5/5/2018
 *
 * A simple (?) client for accessing HTTP resources.
 *
 */

include_once LIBPATH.'php/toonces.php';

class ResourceClient implements iResourceClient {

    var $httpStatus;

    function getHttpStatus() {
        /**
         * Getter method for iResourceClient compliance.
         * @return int $httpStatus - HTTP status code.
         */
        return $this->httpStatus;
    }


    function buildDefaultHeaders() {
        /**
         * This method is designed so subclasses may generate custom headers.
         * @return array $headers - An array of HTTP header strings.
         */
        $headers = array(
             'accept: text/html'
            ,'accept-language: en-US,en;q=0.8'
            ,'user-agent: Toonces'
            ,'content-type: text/html'
        );

        return $headers;
    }


    function get($url, $username = null, $password = null, $paramHeaders = array()) {
        /**
         * Perform an HTTP GET operation.
         * @param string $url - URL to request
         * @param string $username - Optional - Basic Auth user name
         * @param string $password - Optional - Basic Auth password
         * @param array $paramHeaders - Optional - Any custom header strings, arranged in an array. Overrides the default.
         * @return mixed $response - Server's response.
         */

        if (empty($paramHeaders))
            $paramHeaders = $this->buildDefaultHeaders();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $paramHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'identity');

        if ($username && $password)
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

        $response = curl_exec($ch);
        $this->httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (empty($this->httpStatus))
            $this->httpStatus = Enumeration::getOrdinal('HTTP_502_BAD_GATEWAY', 'EnumHTTPResponse');

        curl_close($ch);
        return $response;
    }


    function put($url, $data, $username = null, $password = null, $paramHeaders = array()) {
        /**
         * perform an HTTP PUT operation.
         * @param mixed $data - Body of PUT request.
         * @param string $url - URL to request
         * @param string $username - Optional - Basic Auth user name
         * @param string $password - Optional - Basic Auth password
         * @param array $paramHeaders - Optional - Any custom header strings, arranged in an array. Overrides the default.
         * @return mixed $response - Server's response.
         */

        if (empty($paramHeaders))
            $paramHeaders = $this->buildDefaultHeaders();

        // Initialize cURL
        $ch = curl_init();
        // set options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_ENCODING, 'identity');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($username && $password)
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $paramHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $this->httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (empty($this->httpStatus))
            $this->httpStatus = Enumeration::getOrdinal('HTTP_502_BAD_GATEWAY', 'EnumHTTPResponse');

        curl_close($ch);
        return $response;

    }


    function delete($url, $username = null, $password = null, $paramHeaders = array()) {
        /**
         * perform an HTTP DELETE operation.
         * @param string $url - URL to request
         * @param string $username - Optional - Basic Auth user name
         * @param string $password - Optional - Basic Auth password
         * @param array $paramHeaders - Optional - Any custom header strings, arranged in an array. Overrides the default.
         * @return mixed $response - Server's response.
         */

        if (empty($paramHeaders))
            $paramHeaders = $this->buildDefaultHeaders();

        // Initialize cURL
        $ch = curl_init();
        // set options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_ENCODING, 'identity');

        if ($username && $password)
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $paramHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $this->httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (empty($this->httpStatus))
            $this->httpStatus = Enumeration::getOrdinal('HTTP_502_BAD_GATEWAY', 'EnumHTTPResponse');

        curl_close($ch);
        return $response;
    }
}
