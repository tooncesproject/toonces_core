<?php
/**
 * @author paulanderson
 *
 * Renderer.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Abstract class defining the Renderer abstraction.
 *
 */

require_once LIBPATH . 'php/toonces.php';

abstract class Renderer implements iRenderer
{

    /**
     * @param Response $response
     */
    public function render($response)
    {
        http_response_code($response->responseCode);

        foreach ($response->responseHeaders as $key => $value)
        {
            header($key . ':' . $value);
        }

        $this->transmitBody($response->responseData);

    }


    public function transmitBody($body)
    {
        echo $body;
    }

}
