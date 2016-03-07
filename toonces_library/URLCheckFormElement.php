<?php
/*
 * URLCheckFormElement class
 * 
 * Initial commit: Paul Anderson, 3/6/2016
 * 
 * This FormElement class displays when a user changes the title
 * of a blog page.
 * 
 * It asks the user whether they would like to choose from the following options:
 * 		* Change the title AND the URL
 * 		* Change the title but not the URL
 * 		* Post update without changing URL or nor title
 * 
 */

class URLCheckFormElement extends FormElement implements iElement
{
	
	var $conn;

	public function buildInputArray() {
	
		$changeTitleAndURLButton = new FormElementInput('change', 'radio', $this->formName);
		$this->inputArray['changeBoth'] = $changeTitleAndURLButton;
		$changeTitleAndURLButton->formValue = 0;
		$changeTitleAndURLButton->displayName = 'Change both page title and URL.';
		$changeTitleAndURLButton->setupForm();
		
		$changeOnlyTitleButton = new FormElementInput('change', 'radio', $this->formName);
		$this->inputArray['changeTitle'] = $changeOnlyTitleButton;
		$changeOnlyTitleButton->formValue = 1;
		$changeOnlyTitleButton->displayName = 'Change the title and do not change the URL.';
		$changeOnlyTitleButton->setupForm();
		
		$changeNeitherButton = new FormElementInput('change', 'radio', $this->formName);
		$this->inputArray['changeNeither'] = $changeNeitherButton;
		$changeNeitherButton->formValue = 2;
		$changeNeitherButton->displayName = 'Do not change title nor URL.';
		$changeNeitherButton->setupForm();

		$submitInput = new FormElementInput('submit', 'submit', $this->formName);
		$submitInput->formValue = $this->submitName;
		$submitInput->setupForm();
		$this->inputArray['submit'] = $submitInput;
	}
	
	public function responseStateHandler($responseState) {
		// foo
	}
	
	public function elementAction() {
		
		if ($this->postState == false) {
			
			$parentPath = '';
			
			// Acquire exiting and proposed titles and URLs. 
			$currentPath = '/'.$this->pageViewReference->urlPath;
			$proposedTitle = urldecode($_GET['newtitle']);
			$currentTitle = $this->pageViewReference->pageTitle;
			
			// If no proposed title, the page must have been reached in error.
			// Also, ability to change the page depends on user's access level.
			// In these cases, redirect to the blog page.
			if (empty($proposedTitle) or $this->pageViewReference->userCanEdit == false) {
				$this->send303($currentPath);
			} else {
				// Otherwise...
				// Query the database for the URL path resulting from the proposed title.
				if (isset($this->conn) == false) {
					$this->conn = UniversalConnect::doConnect();
				}
				$sql = "SELECT GENERATE_PATHNAME(?)";
				$stmt = $this->conn->prepare($sql);
				$stmt->execute(array($proposedTitle));
				
				$result = $stmt->fetchAll();
				$proposedURL = $result[0][0];
				
				// Query the database for the path of the page's parent.
				$sql = <<<SQL
				SELECT
					GET_PAGE_PATH(page_id) AS parent_page_path
				FROM
					toonces.page_hierarchy_bridge
				WHERE
					descendant_page_id = ?
SQL;
				$stmt = $this->conn->prepare($sql);
				$stmt->execute(array($this->pageViewReference->pageId));
				
				$result = $stmt->fetchAll();
				$parentPath = $result[0][0];
				$proposedURL = $parentPath.$proposedURL;
				
				// Build HTML.
				$infoHTML = <<<HTML
				<div class="content_container">
				<h2>Change URL and title?</h2>
				<p>The body of the post was updated successfully!</p>
				<p>You have changed the title of an existing blog post.</p>
				<p>Do you want to change its URL also?</p>
				<p>Note: If you change the URL, this page will no longer exist at its present location!</p>
				<p>If this page has been live for some time, changing the URL is not recommended, as any links to the page or user bookmarks will no longer work.</p>
				<p><strong>Current Title: </strong>%s</p>
				<p><strong>Proposed Title: </strong>%s</p>
				<p><strong>Current URL Path: </strong>%s</p>
				<p><strong>Proposed URL Path: </strong>%s</p>
				</div>
HTML;
				$infoHTML = sprintf($infoHTML, $currentTitle, $proposedTitle, $currentPath, $proposedURL).PHP_EOL;
				
				$this->generateFormHTML();
				$this->html = $infoHTML.$this->html;
			}
			
		} else {
			
		}
	}
	public function objectSetup() {
	
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		$this->formName = 'URLCheckForm';
	
		$this->submitName = 'OK!';
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;
	
		}
	}
}