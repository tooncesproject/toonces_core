<?php
/**
 *
 * @author paulanderson
 * DomDataResourcePostApiDataValidator.php
 * Initial Commit: 5/25/18
 *
 * ApiDataValidator for DomResourceDataResource POST operations.
 *
 */

include_once LIBPATH.'php/toonces.php';


class DomDataResourcePostApiDataValidator extends PagePostApiDataValidator implements iApiDataValidator {

    function buildFields() {
        parent::buildFields();
        // Make some fields optional
        $this->fieldValidators['resourceClass']->allowNull = true;

        // Add more fields
        $contentHtmlPathField = new StringFieldValidator(false);
        $contentHtmlPathField->maxLength = 200;
        $this->addFieldValidator('contentHtmlPath', $contentHtmlPathField);

        $contentClientClassField = new StringFieldValidator(true);
        $contentClientClassField->maxLength = 50;
        $this->addFieldValidator('contentClientClass', $contentClientClassField);

        $templateHtmlPathField = new StringFieldValidator(true);
        $templateHtmlPathField->maxLength = 200;
        $this->addFieldValidator('templateHtmlPath', $templateHtmlPathField);

        $templateClientClassField = new StringFieldValidator(false);
        $templateClientClassField->maxLength = 50;
        $this->addFieldValidator('templateClientClass', $templateClientClassField);

    }
}
