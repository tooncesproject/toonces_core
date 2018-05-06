<?php
/**
 * @author paulanderson
 * ResourceClient.php
 * Initial commit: Paul Anderson, 5/5/2018
 * 
 * A simple (?) client for accessing HTTP resources.
 * 
 */

include_once LIBPATH.'php/toonces.php';

class ResourceClient implements iResourceClient {
    
    function get($url, $data, $username = null, $password = null, $headers = array()) {
        
    }
    
    function put($url, $data, $username = null, $password = null, $headers = array()) {
        // Initialize cURL
        $ch = curl_init();
        
        // set options
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_INFILE, $data);
        if ($username && $password)
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
        
    }
    
    function delete($url) {
        
    }
}