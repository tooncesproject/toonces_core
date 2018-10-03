<?php
/**
 * @author paulanderson
 * Date: 9/30/18
 * Time: 9:37 PM
 */

class StaticRequestFactory {

    public static function getActiveRequest() {
        return new Request(
            getallheaders(),
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            $_GET,
            file_get_contents('php://input'),
            $_COOKIE,
            $_SERVER['HTTP_USER_AGENT'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_X_FORWARDED_FOR']
        );
    }
}
