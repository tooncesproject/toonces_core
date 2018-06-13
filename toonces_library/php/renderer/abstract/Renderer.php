<?php
/**
 * @author paulanderson
 *
 * ApiRenderer.php
 * Initial commit: Paul Anderson, 4/25/2018
 *
 * Abstract class providing common functionality for API PageView classes.
 *
 */

require_once LIBPATH . 'php/toonces.php';

abstract class Renderer implements iRenderer
{
    public function renderResource($paramResource) {
        // Not implemented in abstract class.
        // Called by index.php on objects compliant to the iPageView interface.
        // Subclasses should override this.
    }
}
