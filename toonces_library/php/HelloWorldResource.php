<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 10/4/18
 * Time: 6:43 PM
 */

class HelloWorldResource extends Resource
{

    public function __construct()
    {
        $this->getResponder = new HelloWorldResponder($this);
    }

}