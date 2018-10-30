<?php
/**
 * @author paulanderson
 * Initial commit: Paul Anderson, 4/27/2018
 *
 * Abstract class providing common functionality for all Resource subclasses
 *
 */

include_once LIBPATH . 'php/toonces.php';

abstract class Resource
{

    /** @var int */
    public $endpointId;

    /** @var iAuthenticator */
    public $authenticator;

    /** @var Responder */
    public $getResponder;

    /** @var Responder */
    public $postResponder;

    /** @var Responder */
    public $headResponder;

    /** @var Responder */
    public $putResponder;

    /** @var Responder */
    public $deleteResponder;

    /** @var Responder */
    public $connectResponder;

    /** @var Responder */
    public $optionsResponder;

    /** @var Responder */
    public $traceResponder;

    /** @var Responder */
    public $patchResponder;


    /**
     * @param int $paramResourceId
     */
    public function setEndpointId($paramResourceId) {
        $this->endpointId = $paramResourceId;
    }

    /**
     * @return int
     */
    public function getEndpointId()
    {
        return $this->endpointId;
    }

    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function processRequest($paramRequest)
    {

        if (!$this->authenticator)
            $this->authenticator = new DefaultAuthenticator();

        $paramRequest->userId = $this->authenticator->authenticate($paramRequest);

        $httpMethod = $paramRequest->httpMethod;

        switch ($httpMethod) {

            case 'GET':
                return $this->get($paramRequest);
                break;

            case 'POST':
                return $this->post($paramRequest);
                break;

            case 'HEAD':
                return $this->head($paramRequest);
                break;

            case 'PUT':
                return $this->put($paramRequest);
                break;

            case 'DELETE':
                return $this->delete($paramRequest);
                break;

            case 'CONNECT':
                return $this->connect($paramRequest);
                break;

            case 'OPTIONS':
                return $this->options($paramRequest);
                break;

            case 'TRACE':
                return $this->trace($paramRequest);
                break;

            case 'PATCH':
                return $this->patch($paramRequest);
                break;
        }

    }


    /**
     * @param Request $paramRequest
     * @return Response
     * @throws Exception
     */
    public function get($paramRequest)
    {
        if (!$this->getResponder)
            $this->getResponder = new DefaultResponder($this);

        $response = $this->getResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function post($paramRequest)
    {
        if (!$this->postResponder)
            $this->postResponder = new DefaultResponder($this);

        $response = $this->postResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function head($paramRequest)
    {
        if (!$this->headResponder)
            $this->headResponder = new DefaultResponder($this);

        $response = $this->headResponder->respond($paramRequest);
        return $response;// Override to define the resource's response to a HEAD request.
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function put($paramRequest)
    {
        if (!$this->putResponder)
            $this->putResponder = new DefaultResponder($this);

        $response = $this->putResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function delete($paramRequest)
    {
        if (!$this->deleteResponder)
            $this->deleteResponder = new DefaultResponder($this);

        $response = $this->deleteResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function connect($paramRequest)
    {
        if (!$this->connectResponder)
            $this->connectResponder = new DefaultResponder($this);

        $response = $this->connectResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function options($paramRequest)
    {
        if (!$this->optionsResponder)
            $this->optionsResponder = new DefaultResponder($this);

        $response = $this->optionsResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function trace($paramRequest)
    {
        if (!$this->traceResponder)
            $this->traceResponder = new DefaultResponder($this);

        $response = $this->traceResponder->respond($paramRequest);
        return $response;
    }


    /**
     * @param Request $paramRequest
     * @return Response
     */
    public function patch($paramRequest)
    {
        if (!$this->patchResponder)
            $this->patchResponder = new DefaultResponder($this);

        $response = $this->patchResponder->respond($paramRequest);
        return $response;
    }

}
