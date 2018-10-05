<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 10/4/18
 * Time: 6:49 PM
 */

class FourOhFourResource extends Resource {

    public function __construct()
    {
        $this->getResponder = new FourOhFourResponder();
    }
}