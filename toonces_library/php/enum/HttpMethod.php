<?php
/**
 * @author paulanderson
 * Date: 10/1/18
 * Time: 9:05 PM
 */

class HttpMethod extends SplEnum
{

    const __default = self::GET;

    const GET = 0;
    const POST = 1;
    const HEAD = 2;
    const PUT = 3;
    const DELETE = 4;
    const CONNECT = 5;
    const OPTIONS = 6;
    const TRACE = 7;
    const PATCH = 8;

}
