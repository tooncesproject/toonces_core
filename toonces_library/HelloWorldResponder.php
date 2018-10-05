<?php
/**
 * Created by PhpStorm.
 * User: paulanderson
 * Date: 10/4/18
 * Time: 5:58 PM
 */

class HelloWorldResponder extends Responder
{
    public function respond($paramRequest) {
        $html = <<<HTML
        <HTML>
            <body>
            <h1>Hello World!</h1>
            
            </body>
        </HTML>

HTML;

        $response = new DefaultResponse(200, null, $html);
        return $response;
    }
}
