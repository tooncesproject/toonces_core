<?php
/**
 * @author paulanderson
 * Initial commit: 6/17/18
 */

class TooncesWelcomeDomDocumentResource extends SimpleDomDocumentResource {

    public function getResource() {

        $this->outerDomDocumentPath = ROOTPATH . 'html/toonces_default_template.html';
        $this->innerDomDocumentPath = ROOTPATH . 'html/toonces_welcome.html';

        return parent::getResource();

    }

}