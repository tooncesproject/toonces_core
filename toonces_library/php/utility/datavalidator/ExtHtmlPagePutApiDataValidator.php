<?php
/**
 *
 * @author paulanderson
 * ExtHtmlPagePutApiDataValidator.php
 * Initial Commit: 5/25/18
 *
 * ApiDataValidator for ExtHtmlPageDataResource PUT operations.
 *
 */

include_once LIBPATH.'php/toonces.php';

class ExtHtmlPagePutApiDataValidator extends ExtHtmlPagePostApiDataValidator implements iApiDataValidator {

    function buildFields() {
        parent::buildFields();

        // Make some fields optional.
        $this->fieldValidators['htmlBody']->allowNull = true;
        $this->fieldValidators['ancestorResourceId']->allowNull = true;
        $this->fieldValidators['pageTitle']->allowNull = true;
    }
}
