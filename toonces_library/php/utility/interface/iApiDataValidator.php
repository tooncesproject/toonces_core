<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 5/25/18
 * Time: 12:06 PM
 */

include_once LIBPATH . 'php/toonces.php';

interface iApiDataValidator {

    public function buildFields();
    public function validateDataStructure($paramData);
    public function getMissingRequiredFields($paramData);
    public function getInvalidFields($paramData);


}