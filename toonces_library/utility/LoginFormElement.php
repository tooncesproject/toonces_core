<?php

class LoginFormElement extends FormElement implements iElement
{
	
	// Inherited variables commented out
	// var $html;
	// var $htmlHeader;
	// var $htmlFooter;
	// var $pageViewReference;

	public function __construct($pageView) {
		$this->pageViewReference = $pageView;
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		
		$this->html = $this->formHTML();
		
		// If login was not successful, display the login fail message.
		if (isset($this->pageViewReference->loginSuccess) and $this->pageViewReference->loginSuccess == 0) {
			$message = '<div class="form_message_notifiacation"><p>ACCESS DENIED. GO AWAY. Or try again.</p></div>';
			
			$this->html = $message.PHP_EOL.$this->html;
		}
		
	}

	function formHTML() {
		
		$html = <<<HTML
            <form id="login" method="post">
                Username:<br>
                <input type="text" name="username" size="50">
                <br>
                <br>
                Password:<br>
                <input type="password" name="psw" size="50">
                <br>
                <br>
                <input type="submit" value="Shit yeah!"/>
            </form>
HTML;
	
		return $html;
	}
	
	
}