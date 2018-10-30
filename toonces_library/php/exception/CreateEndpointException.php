<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 10/11/18
 * Time: 3:22 PM
 */

class CreateEndpointException extends TooncesException
{
    public function __construct($message, $code = 1, Exception $previous = null)
    {
        $this->tooncesExceptionId = 1;
        parent::__construct($message, $this->tooncesExceptionId, $previous);
    }

}
