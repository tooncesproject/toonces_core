<?php
/*
 * DeleteBlogPostFormElement
 * 
 * Initial commit: Paul Anderson, 3/20/2016
 * When a blog post page goes to delete mode,
 * this page asks for confirmation.
 * 
 */

class DeleteBlogPostFormElement extends FormElement implements iElement
{
	
	function buildInputArray() {
		
		$confirmationInput = new FormElementInput('confirmation', 'text', $this->formName);
		$this->inputArray['confirmation'] = $confirmationInput;
		$confirmationInput->displayName = 'Type "YES" (case sensitive) if you\'re sure you want to delete this blog post.';
		$confirmationInput->setupForm();
		
		$submitInput = new FormElementInput('Submit', 'submit', $this->formName);
		$submitInput->formValue = $this->submitName;
		$submitInput->setupForm();
		$this->inputArray['submit'] = $submitInput;
		
	}
	
	function responseStateHandler($responseState) {
		
		$params = array();
		$queryString = '';
		
		switch ($responseState) {
			case 1:
				// If the post was deleted, go to its parent page.
				$uri = GrabParentPageURL::getURL($this->pageViewReference->pageId);
				$this->send303($uri);
				break;
			default:
				// exit delete mode
				$urlArray = parse_url($_SERVER['REQUEST_URI']);
				// If there's a query string, parse it too.
				parse_str($urlArray['query'], $params);
				unset($params['mode']);
				if (empty($params) == false)
					$queryString = '?'.http_build_query($params);
				$uri = $urlArray['path'].$queryString;
				
				$this->send303($uri);
				
		}
	}
	
	function elementAction() {

		if (isset($this->conn) == false) 
			$this->conn = UniversalConnect::doConnect();
		
		// stupid shit i should refactor
		if ($this->postState == false) {
		
			$queryString = '';
			// build query to exit delete mode
			$urlArray = parse_url($_SERVER['REQUEST_URI']);
			// If there's a query string, parse it too.
			parse_str($urlArray['query'], $params);
			unset($params['mode']);
			if (empty($params) == false)
				$queryString = '?'.http_build_query($params);
			
			$uri = $urlArray['path'].$queryString;

			$html = <<<HTML
			<div class="content_container">
			<h2>Delete blog post?</h2>
			<p>DO YOU REALLY WANT TO?</p>
			<p><a href="%s">Nah, not really.</a></p>
			</div>
HTML;
			
			$html = sprintf($html,$uri);
			
			$this->generateFormHTML();
			$this->html = $html.$this->html; 
		} else {
			// If there is POST data, validate and evaluate.
			
			if ($this->inputArray['confirmation']->postData == 'YES') {
				// Soft-delete the blog post.
				$sql = <<<SQL
				UPDATE blog_posts
				SET 
					 deleted = CURRENT_TIMESTAMP()
					,published = 0
				WHERE page_id = %d
SQL;
				$sql = sprintf($sql,$this->pageViewReference->pageId);
				$this->conn->query($sql);
				
				// Unpublish the page.
				$sql = <<<SQL
				UPDATE pages
				SET published = 0
				WHERE page_id = %d
SQL;
				$sql = sprintf($sql, $this->pageViewReference->pageId);
				$this->conn->query($sql);
				
				$this->responseStateHandler(1);
			} else {
				//If YES not typed, this will exit edit mode.
				$this->responseStateHandler(0);
			}
			
			
		}
			
		
	}
	
	public function objectSetup() {
	
		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		$this->formName = 'deleteBlogPostForm';
	
		$this->submitName = 'Submit';
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;
	
		}
	}
}