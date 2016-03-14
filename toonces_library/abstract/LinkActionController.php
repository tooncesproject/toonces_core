<?php
/*
 * LinkActionController
 * Initial commit: Paul Anderson, 2013-03-13
 * 
 * This abstract class provides a framework for HTTP GET-based input 
 * triggering state change in the stack.
 * 
 * It establishes a PRD-like design pattern for GET calls. It detects a "linkaction" variable
 * in the URL query string and calls any functions expected to be triggered by the named
 * action.
 * 
 * In order to work, a LinkActionController object must be instantiated in the scope of the
 * link's destination page build. LinkAction responses are not intended to apply in global scope.
 * It also must hold the linkActionName variable specified in the URI.
 * 
 */

abstract class LinkActionController
{
	public $linkActionName;
	public $redirectURI;
	public $urlArray;
	public $params;
	
	public function __construct($linkActionName, $performAction = true) {
		// Object must be instantiated with the named action.
		// This method detects whether the linkActionName exists in the URI.
		// By default, it calls the linkAction method if so. 
		$this->linkActionName = $linkActionName;
		
		// Parse the URL.
		$this->urlArray = parse_url($_SERVER['REQUEST_URI']);
		// If there's a query string, parse it too.
		if (isset($this->urlArray['query']))
			parse_str($this->urlArray['query'], $this->params);
		
		//$actionDetected = array_key_exists($linkActionName, $this->params);
		$actionDetected = false;
		
		if (isset($this->params['linkaction'])) {
			if ($this->params['linkaction'] == $linkActionName) {
				$actionDetected == true;
			}
		}
		
		// Default behavior is to perform LinkAction
		if ($actionDetected && $performAction)
			$this->linkAction();
		
	}

	
	// This function redirects the user based on class settings or
	// the current URI. If you want to specify a URI, you must set
	// the $redirectURI class variable.
	function redirectUser() {
	
		$link = '';
		
		// By default, URI is current page, sans the linkaction parameter.
		
		// If the URI has been set externally, use that. 
		if (isset($this->redirectURI)) {
			$link = $this->urlArray['path'].$this->redirectURI;
		} else {
			$redirectParams = $this->params;
			unset($redirectParams[$this->linkActionName]);
		}

		// If this makes the array empty, Build URL without parameters
		if (empty($redirectParams)) {
			$link = $this->urlArray['path'];
		
		// Otherwise, build the url with the parameters.
		} else {
			$link = $this->urlArray['path'].'?'.http_build_query($redirectParams);
		}

		$link = "http://$_SERVER[HTTP_HOST]$uri";
		header("HTTP/1.1 303 See Other");
		header('Location: '.$link);
	}
	
	
	public function linkAction() {
		// Performs any under-the-hood actions specified by the named action.
		// To be customized by children of LinkActionController.
	}
}