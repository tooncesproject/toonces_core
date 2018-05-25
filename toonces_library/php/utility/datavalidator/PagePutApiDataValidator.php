<?php
/**
 * @author paulanderson
 * PagePutApiDataValidator.php
 * Initial Commit: 5/25/18
 *
 * Extends PagePostApiDataValidator, allowing nulls in some fields.
 *
 */

include_once LIBPATH . 'php/toonces.php';

class PagePutApiDataValidator extends PagePostApiDataValidator implements iApiDataValidator {

    public function buildFields() {

        parent::buildFields();

        // Allow nulls on certain fields
        $this->fieldValidators['ancestorPageId']->allowNull = true;
        $this->fieldValidators['pageTitle']->allowNull = true;
        $this->fieldValidators['pageBuilderClass']->allowNull = true;
        $this->fieldValidators['pageViewClass']->allowNull = true;
        $this->fieldValidators['published']->allowNull = true;

    }
}
