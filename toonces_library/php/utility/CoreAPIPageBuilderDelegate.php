<?php
/*
 * CoreAPIPageBuilderDelegate.php
 * Initial commit: Paul Anderson, 2/28/2018
 * 
 * Delegate handling authentication and versisioning for the Toonces Core API.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class CoreAPIPageBuilderDelegate extends APIPageBuilderDelegate implements iAPIPageBuilderDelegate {
 
    var $pageView;
    
    function __construct($paramPageView) {
        $this->pageView = $paramPageView;
    }
    
    function authenticateUser() {
        // Toonces Core Services API uses Basic Auth for authentication, and the same 
        // user structure as Toonces Admin.
        $userID = NULL;
        if (array_key_exists('PHP_AUTH_USER', $_SERVER) && array_key_exists('PHP_AUTH_PW', $_SERVER) ) {
            $email = $_SERVER['PHP_AUTH_USER'];
            $pw = $_SERVER['PHP_AUTH_PW'];
            $sessionManager = $this->pageView->sessionManager;
            
            $loginSuccess = $sessionManager->login($email, $pw);
            if ($loginSuccess)
                $userID = $sessionManager->userId;
        }

        return $userId;
    }

}