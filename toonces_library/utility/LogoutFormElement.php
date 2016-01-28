<?php
/*
 * LogoutFormElement
 * Initial commit: Paul Anderson 1/28/2016
 * 
 */
class LogoutFormElement extends FormElement
{


	function buildInputArray() {
		$logoutInputElement = new FormElementInput('logout', 'hidden');
		$this->inputArray['logout'] = $logoutInputElement;

	}

	function elementAction() {
		$this->formName = 'logoutForm';
		$this->generateFormHTML();
		if ($this->postState == 1) {
			$this->pageViewReference->sessionManager->logout();
			$this->responseStateHandler(0);
		}
	}
}