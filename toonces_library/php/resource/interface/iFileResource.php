<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

require_once LIBPATH . 'php/toonces.php';

interface iFileResource extends iResource {

    /**
     * @return string
     */
    public function getResource();

}
