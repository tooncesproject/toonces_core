<?php
/**
 * @author paulanderson
 * Date: 10/1/18
 * Time: 2:59 PM
 */

interface iResponder {

    /**
     * @return Response
     */
    public function respond($paramRequest);

}