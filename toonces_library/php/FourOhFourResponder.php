<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 10/4/18
 * Time: 6:49 PM
 */

class FourOhFourResponder extends Responder {
    public function respond($paramRequest) {
        $html = <<<HTML
        <HTML>
            <body>
            <h1>404 BITCH</h1>
            
            </body>
        </HTML>

HTML;

        $response = new DefaultResponse(404, null, $html);
        return $response;
    }

}