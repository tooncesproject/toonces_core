<?php
/*
 * LinkActionControlElement
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

abstract class LinkActionControlElement extends Element implements iElement
{
	public $linkActionName;
	public $redirectURI;
	public $urlArray;
	public $params;

	public function __construct($pageViewReference) {

		$this->pageViewReference = $pageViewReference;

		// This method detects whether the linkActionName exists in the URI.
		// By default, it calls the linkAction method if so.

		// Parse the URL.
		$this->urlArray = parse_url($_SERVER['REQUEST_URI']);
		// If there's a query string, parse it too.
		if (isset($this->urlArray['query']))
			parse_str($this->urlArray['query'], $this->params);
	}



	// This function redirects the user based on class settings or
	// the current URI. If you want to specify a URI, you must set
	// the $redirectURI class variable.
	function redirectUser() {

		$uri= '';

		// By default, URI is current page, sans the linkaction parameter.

		// If the URI has been set externally, use that.
		if (isset($this->redirectURI)) {
			$uri = $this->urlArray['path'].$this->redirectURI;
		} else {
			$redirectParams = $this->params;
			unset($redirectParams['linkaction']);
		}

		// If this makes the array empty, Build URL without parameters
		if (empty($redirectParams)) {
			$uri = $this->urlArray['path'];

		// Otherwise, build the url with the parameters.
		} else {
			$uri = $this->urlArray['path'].'?'.http_build_query($redirectParams);
		}

		$link = "http://$_SERVER[HTTP_HOST]$uri";
		header("HTTP/1.1 303 See Other");
		header('Location: '.$link);

	}


	public function linkAction() {
	// To be customized by children of LinkActionController.
	// Responsibilities:
		// Performs any under-the-hood actions specified by the named action.


	}

	public function objectSetup() {
		// Post-instantiation actions here.
	}

	public function getHTML() {
		// linkActionName must be set by either the objectSetup method or the instantiating function.
		// If linkActionName is not set, throw an exception.
		// Otherwise, execute the action.
		$this->objectSetup();

		if (isset($this->linkActionName) == false) {
			throw new Exception('LinkActionController Exception - linkActionName (string) class variable MUST be set for children of LinkActionController abstract class at time of calling the getHTML() method.');
		} else {

			$actionDetected = false;

			if (isset($this->params['linkaction'])) {
				if ($this->params['linkaction'] == $this->linkActionName) {
					$actionDetected = true;
				}
			}

			// Default behavior is to perform LinkAction
			if ($actionDetected == true)
				$this->linkAction();
		}

		// returns no HTML
		return '';
	}
}