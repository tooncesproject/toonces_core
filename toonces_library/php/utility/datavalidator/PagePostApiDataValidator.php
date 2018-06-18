<?php
/**
 * PagePostApiDataValidator.php
 * Initial Commit: 5/25/18
 * @author paulanderson
 *
 * Implementation of ApiDataValidator for POST operations in ResourceDataResource.
 */


include_once LIBPATH . 'php/toonces.php';

class PagePostApiDataValidator extends ApiDataValidator implements iApiDataValidator {

    /**
     * @throws Exception
     */
    function buildFields() {

        $ancestorResourceId = new IntegerFieldValidator(false);
        $this->addFieldValidator('ancestorResourceId', $ancestorResourceId);

        $pathName = new StringFieldValidator(true);
        $pathName->maxLength = 50;
        $this->addFieldValidator('pathName', $pathName);

        $resourceClass= new StringFieldValidator(false);
        $resourceClass->maxLength = 50;
        $this->addFieldValidator('resourceClass', $resourceClass);

        $redirectOnError = new BooleanFieldValidator(true);
        $this->addFieldValidator('redirectOnError', $redirectOnError);

        $published = new BooleanFieldValidator(true);
        $this->addFieldValidator('published', $published);

    }
}
