<?php
/**
 * @author paulanderson
 * Date: 10/29/18
 * Time: 5:58 PM
 */

class EndpointNotFoundException extends TooncesException
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $this->tooncesExceptionId = 3;
        parent::__construct($message, $this->tooncesExceptionId, $previous);
    }

}