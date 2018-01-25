<?php
/*
 * ToolbarElement
 * Initial Commit Paul Anderson 2.4.2016
 *
 * Abstraction for toolbar UI objects.
 *
 */
abstract class ToolbarElement extends ViewElement
{

	public $utilityCssClass;
	public $userId;
	public $userNickname;
	public $userCanEdit;
	public $userIsAdmin;
	public $toolElement;

	public function __construct($pageView) {
		// Required stuff
		$this->pageViewReference = $pageView;

		// Default utilityCssClass
		$this->utilityCssClass = 'TE_toolbarelement';

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

		// set header and footer
		$headerTemplate = '<div class = "%s">';
		$this->htmlHeader = sprintf($headerTemplate,$this->utilityCssClass).PHP_EOL;
		$this->htmlFooter = '</div>'.PHP_EOL;

		// Add elements to the array
		$logoutFormElement = new LogoutFormElement($this->pageViewReference);
		$this->addElement($logoutFormElement);

		$utilityElement = new Element($this->pageViewReference);
		$utilityElement->html = $this->buildUtilityHTML();
		$this->addElement($utilityElement);

		$this->toolElement = new ViewElement($this->pageViewReference);
		$this->buildToolElement();

		$this->addElement($this->toolElement);

	}

	// Builds the basic funtionality common to all toolbar elements.
	public function buildUtilityHTML() {
		// Include the logout form element
		//$logoutFormElement = new LogoutFormElement($this->pageViewReference);
		//$this->html = $this->html.$logoutFormElement->getResource();

		// HTML Template
		$htmlTemplate = <<<HTML
		<div class="TE_usergreeting">
    		<p>Greetings, %s.</p>
        	<p><a href="/admin"><telink>Go to Toonces Admin</telink></a></p>
        	<p><a href="javascript: submitform()"><telink>Sign Out</telink>	</a></p>
     	</div>

HTML;

		// populate the template
		$htmlOut = sprintf($htmlTemplate,$this->userNickname);

		return $htmlOut;
	}

	public function buildToolElement() {
		// abstract. Chilren should overrride this.

		// This element's job is to add elements to the toolElement ViewElement object.

		// The toolElement ViewElement object should already exist once this is
		// called.

	}

}