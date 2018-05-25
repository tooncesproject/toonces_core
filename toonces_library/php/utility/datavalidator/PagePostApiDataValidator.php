<?php
/**
 * PagePostApiDataValidator.php
 * Initial Commit: 5/25/18
 * @author paulanderson
 *
 * Implementation of ApiDataValidator for POST operations in PageDataResource.
 */


include_once LIBPATH . 'php/toonces.php';

class PagePostApiDataValidator extends ApiDataValidator implements iApiDataValidator {

    /**
     * @throws Exception
     */
    function buildFields() {

        $ancestorPageId = new IntegerFieldValidator(false);
        $this->addFieldValidator('ancestorPageId', $ancestorPageId);

        $pathName = new StringFieldValidator(true);
        $pathName->maxLength = 50;
        $this->addFieldValidator('pathName', $pathName);

        $pageTitle = new StringFieldValidator(false);
        $pageTitle->maxLength = 50;
        $this->addFieldValidator('pageTitle', $pageTitle);

        $pageLinkText = new StringFieldValidator(true);
        $pageLinkText->maxLength = 50;
        $this->addFieldValidator('pageLinkText', $pageLinkText);

        $pageBuilderClass= new StringFieldValidator(false);
        $pageBuilderClass->maxLength = 50;
        $this->addFieldValidator('pageBuilderClass', $pageBuilderClass);

        $pageViewClass = new StringFieldValidator(false);
        $pageViewClass->maxLength = 50;
        $this->addFieldValidator('pageViewClass', $pageViewClass);

        $redirectOnError = new BooleanFieldValidator(true);
        $this->addFieldValidator('redirectOnError', $redirectOnError);

        $published = new BooleanFieldValidator(true);
        $this->addFieldValidator('published', $published);

        $pageTypeId = new IntegerFieldValidator(true);
        $this->addFieldValidator('pageTypeId', $pageTypeId);
    }
}
