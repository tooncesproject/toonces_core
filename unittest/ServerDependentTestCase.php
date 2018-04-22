<?php
/*
 * @author paulanderson
 * ServerDependentTestCase.php
 * Initial Commit: Paul Anderson, 4/21/2018
 * 
 * Extends PHPUnit TestCase to provide dependency injection for Apache server variables.
 *  
 */

use PHPUnit\Framework\TestCase;

abstract class ServerDependentTestCase extends TestCase {
    
    // lifted from https://github.com/tymondesigns/jwt-auth/issues/28
    function apiServerFixture($method, $uri, array $data = [], array $headers = [])
    {
        //if ($this->token && !isset($headers['Content-type'])) {
        //    $headers['Content-type'] = "Bearer: $this->token";
        //}
        
        $server = $this->transformHeadersToServerVars($headers);
        
        $this->call(strtoupper($method), $uri, $data, [], [], $server);
        
        return $this;
    }
}