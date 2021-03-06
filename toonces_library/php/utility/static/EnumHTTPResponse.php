<?php
/*
 * EnumHTTPResponse.php
 * Enumerates standard HTTP response codes
*/

require_once LIBPATH.'php/toonces.php';

class EnumHTTPResponse extends Enumeration {

    public static $enum = array
    (
         100 => 'HTTP_100_CONTINUE'
        ,101 => 'HTTP_101_SWITCHING_PROTOCOLS'
        ,200 => 'HTTP_200_OK'
        ,201 => 'HTTP_201_CREATED'
        ,202 => 'HTTP_202_ACCEPTED'
        ,203 => 'HTTP_203_NON-AUTHORITATIVE_INFORMATION'
        ,204 => 'HTTP_204_NO_CONTENT'
        ,205 => 'HTTP_205_RESET_CONTENT'
        ,206 => 'HTTP_206_PARTIAL_CONTENT'
        ,300 => 'HTTP_300_MULTIPLE_CHOICES'
        ,301 => 'HTTP_301_MOVED_PERMANENTLY'
        ,302 => 'HTTP_302_FOUND'
        ,303 => 'HTTP_303_SEE_OTHER'
        ,304 => 'HTTP_304_NOT_MODIFIED'
        ,307 => 'HTTP_307_TEMPORARY_REDIRECT'
        ,308 => 'HTTP_308_PERMANENT_REDIRECT'
        ,400 => 'HTTP_400_BAD_REQUEST'
        ,401 => 'HTTP_401_UNAUTHORIZED'
        ,403 => 'HTTP_403_FORBIDDEN'
        ,404 => 'HTTP_404_NOT_FOUND'
        ,405 => 'HTTP_405_METHOD_NOT_ALLOWED'
        ,406 => 'HTTP_406_NOT_ACCEPTABLE'
        ,407 => 'HTTP_407_PROXY_AUTHENTICATION_REQUIRED'
        ,408 => 'HTTP_408_REQUEST_TIMEOUT'
        ,409 => 'HTTP_409_CONFLICT'
        ,410 => 'HTTP_410_GONE'
        ,411 => 'HTTP_411_LENGTH_REQUIRED'
        ,412 => 'HTTP_412_PRECONDITION_FAILED'
        ,413 => 'HTTP_413_PAYLOAD_TOO_LARGE'
        ,414 => 'HTTP_414_URI_TOO_LONG'
        ,415 => 'HTTP_415_UNSUPPORTED_MEDIA_TYPE'
        ,416 => 'HTTP_416_RANGE_NOT_SATISFIABLE'
        ,417 => 'HTTP_417_EXPECTATION_FAILED'
        ,418 => 'HTTP_418_IM_A_TEAPOT'
        ,426 => 'HTTP_426_UPGRADE_REQUIRED'
        ,428 => 'HTTP_428_PRECONDITION_REQUIRED'
        ,429 => 'HTTP_429_TOO_MANY_REQUESTS'
        ,431 => 'HTTP_431_REQUEST_HEADER_FIELDS_TOO_LARGE'
        ,451 => 'HTTP_451_UNAVAILABLE_FOR_LEGAL_REASONS'
        ,500 => 'HTTP_500_INTERNAL_SERVER_ERROR'
        ,501 => 'HTTP_501_NOT_IMPLEMENTED'
        ,502 => 'HTTP_502_BAD_GATEWAY'
        ,503 => 'HTTP_503_SERVICE_UNAVAILABLE'
        ,504 => 'HTTP_504_GATEWAY_TIMEOUT'
        ,505 => 'HTTP_505_HTTP_VERSION_NOT_SUPPORTED'
        ,511 => 'HTTP_511_NETWORK_AUTHENTICATION_REQUIRED'

    );
}