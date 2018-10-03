<?php
/**
 * @author paulanderson
 * Date: 9/30/18
 * Time: 3:05 PM
 */

class Request
{

    public $headers;
    public $httpMethod;
    public $uri;
    public $parameters;
    public $body;
    public $cookies;
    public $userAgent;
    public $ipAddress;
    public $httpXForwardedFor;
    public $userId;


    /**
     * Request constructor.
     * @param array $paramHeaders
     * @param HttpMethod $paramHttpMethod
     * @param string $paramUri
     * @param array $paramParameters
     * @param string $paramBody
     * @param array $paramCookies
     * @param string $paramUserAgent
     * @param int $paramIpAddress
     * @param int $paramHttpXForwardedFor
     */
    public function __construct(
        $paramHeaders,
        $paramHttpMethod,
        $paramUri,
        $paramParameters,
        $paramBody,
        $paramCookies,
        $paramUserAgent,
        $paramIpAddress,
        $paramHttpXForwardedFor
    )
    {

        $this->headers = $paramHeaders;
        $this->httpMethod = $paramHttpMethod;
        $this->uri = $paramUri;
        $this->parameters = $paramParameters;
        $this->body = $paramBody;
        $this->cookies = $paramCookies;
        $this->userAgent = $paramUserAgent;
        $this->ipAddress = $paramIpAddress;
        $this->httpXForwardedFor = $paramHttpXForwardedFor;

    }

}
