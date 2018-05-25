<?php
/**
 *
 * @author paulanderson
 * ExtHtmlPagePostApiDataValidator.php
 * Initial Commit: 5/25/18
 *
 * ApiDataValidator for ExtHtmlPageDataResource POST operations.
 *
 */

include_once LIBPATH.'php/toonces.php';


class ExtHtmlPagePostApiDataValidator extends PagePostApiDataValidator implements iApiDataValidator {

    function buildFields() {
        parent::buildFields();
        // Make some fields optional
        $this->fieldValidators['pageBuilderClass']->allowNull = true;
        $this->fieldValidators['pageViewClass']->allowNull = true;

        // Add a field for the HTML body
        $htmlBodyField = new HtmlFieldValidator(false);
        $this->addFieldValidator('htmlBody', $htmlBodyField);

        // Add a field for the Client class
        $clientClassField = new StringFieldValidator(true);
        $this->addFieldValidator('clientClass', $clientClassField);
    }
}
