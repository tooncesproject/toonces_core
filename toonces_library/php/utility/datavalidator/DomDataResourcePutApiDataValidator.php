<?php
/**
 *
 * @author paulanderson
 * DomDataResourcePutApiDataValidator.php
 * Initial Commit: 5/25/18
 *
 * ApiDataValidator for DomResourceDataResource PUT operations.
 *
 */

include_once LIBPATH.'php/toonces.php';

class DomDataResourcePutApiDataValidator extends DomDataResourcePostApiDataValidator implements iApiDataValidator {

    function buildFields() {
        parent::buildFields();

        // Make some fields optional.
        $this->fieldValidators['htmlBody']->allowNull = true;
        $this->fieldValidators['ancestorResourceId']->allowNull = true;
    }
}
