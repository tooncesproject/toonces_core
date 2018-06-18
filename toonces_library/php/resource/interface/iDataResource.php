<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

require_once LIBPATH . 'php/toonces.php';

interface iDataResource extends iResource {

    /**
     * @return array
     */
    public function getResource();

}
