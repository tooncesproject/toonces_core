<?php
/**
 * @author paulanderson
 * Date: 10/11/18
 * Time: 5:58 PM
 */

class XmlReadWriteException extends TooncesException
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $this->tooncesExceptionId = 2;
        parent::__construct($message, $this->tooncesExceptionId, $previous);
    }

}