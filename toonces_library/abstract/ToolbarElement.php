<?php
/*
 * ToolbarElement
 * Initial Commit Paul Anderson 2.4.2016
 * 
 * Abstraction for toolbar UI objects.
 * 
 */
abstract class ToolbarElement extends Element
{

	public $utilityCssClass;
	public $userId;
	public $userNickname;
	public $userCanEdit;
	public $userIsAdmin;

	public function __construct($pageView) {
		// Required stuff
		$this->pageViewReference = $pageView;
		
		// populate user variables
		$this->userId = $this->pageViewReference->sessionManager->userId;
		$this->userNickname = $this->pageViewReference->sessionManager->nickname;
		$this->userIsAdmin = $this->pageViewReference->sessionManager->userIsAdmin; 
		
		// If the user is admin, can edit defaults to true
		// Otherwise, refer to the PageView object
		if ($this->userIsAdmin == true) {
			$this->userCanEdit = true;
		} else {
			$this->userCanEdit = $this->pageViewReference->userCanEdit;
		}

	}
	// Builds the basic funtionality common to all toolbar elements.
	public function buildUtilityHTML() {
		// Include the logout form element
		$logoutFormElement = new LogoutFormElement();
		$this->html = $this->html.$logoutFormElement->getHTML();

		// HTML Template
		$htmlTemplate = <<<HTML
		<div class="%s">
			
		</div>
HTML;

		// populate the template
		$htmlTemplate = sprintf($htmlTemplate,$this->utilityCssClass);
		
	}
}