<?php
/**
 * @author paulanderson
 * Date: 10/2/18
 * Time: 7:51 PM
 */

class DefaultResponder extends Responder
{
    public function respond($paramRequest) {
        return new DefaultResponse(
            new HttpResponseCode(HttpResponseCode::HTTP_405_METHOD_NOT_ALLOWED),
            null,
            null
        );

    }
}
