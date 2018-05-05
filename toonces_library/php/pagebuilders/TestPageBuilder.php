<?php
/**
 * @author paulanderson
 * TestPageBuilder.php
 * Initial Commit: 5/4/2018
 *
 * A generic, independent StandardPageBuilder subclass for testing purposes.
 *
 */

include_once LIBPATH.'php/toonces.php';

class TestPageBuilder extends StandardPageBuilder {

    function createContentElement() {

        $element = new HTMLResource($this->pageViewReference);
        // just a little HTML for your dome
        $html = <<<HTML
        <div class="copy_block">
            <h1>Toonces Test Page!</h1>
            <p>Yup, it's a page. Enjoy.</p>
            <iframe width="500" height="315" src="https://www.youtube.com/embed/NjVugzSR7HA" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
HTML;
        $element->html = $html;
        $this->contentElement = $element;

    }
}
