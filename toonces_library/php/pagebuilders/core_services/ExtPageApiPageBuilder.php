<?php
/**
 * @author paulanderson
 * ExtPageApiPageBuilder.php
 * Initial commit: Paul Anderson, 5/13/2018
 *
 * ApiPageBuilder subclass creating an ExtHtmlPageResource object/endpoint.
 *
 */

require_once LIBPATH.'php/toonces.php';

class ExtPageApiPageBuilder extends APIPageBuilder {

    function buildPage() {

        // It's an ExtHtmlPageResource
        $ehpr = new ExtHtmlPageResource($this->pageViewReference);
        array_push($this->resourceArray, $ehpr);
        return $this->resourceArray;

    }
}
