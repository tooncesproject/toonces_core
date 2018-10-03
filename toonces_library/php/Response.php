<?php
/**
 * @author paulanderson
 * Date: 10/1/18
 * Time: 9:50 PM
 */

abstract class Response
{

    var $responseCode;
    var $responseHeaders;
    var $responseData;

    /**
     * Response constructor.
     * @param HttpResponseCode $paramResponseCode
     * @param array $paramResponseHeaders
     * @param $paramResponseData
     */
    public function __construct($paramResponseCode,
                                $paramResponseHeaders,
                                $paramResponseData
    )
    {
        $this->responseCode = $paramResponseCode;
        $this->responseHeaders = $paramResponseHeaders;
        $this->responseData = $paramResponseData;

    }

    public function getResponseData()
    {
        return $this->responseData;
    }

    public function render()
    {
        http_response_code($this->responseCode);

        foreach ($this->responseHeaders as $key => $value)
        {
            header($key . ':' . $value);
        }

        if ($this->responseData)
            $this->transmitResponseData();

    }

    private function transmitResponseData()
    {
        echo $this->responseData;
    }

}
