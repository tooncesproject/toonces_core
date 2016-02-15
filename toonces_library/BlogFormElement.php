<?php
/*
 * BlogFormElement
 * Initial commit: Paul Anderson 2/14/2016
 * 
 */

class BlogFormElement extends FormElement implements iElement
{

	var $conn;
	var $newPageURI = '';
	var $newPageId;

	public function generateFormHTML() {
		// Overridden in this case so we can add our textarea
		// Iterate through input objects to generate HTML.
		$formNameHTML = '';
		$messageHTML = '';

		if (isset($this->formName) == false)
			throw new Exception('Form name must be set.');

		$this->messageVarName = $this->formName.'_msg';
		if (isset($_SESSION[$this->messageVarName]))
			$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';

		$formNameHTML = 'name="'.$this->formName.'"';
		$formIdHTML = 'id="'.$this->formName.'"';

		$this->html = $messageHTML.PHP_EOL;

		$this->html = $this->html.'<form method="post" '.$formNameHTML.' '.$formIdHTML.'>';
		foreach ($this->inputArray as $inputObject) {
			if ($inputObject->hideInput == false)
				$this->html = $this->html.$inputObject->html;
		}

		$this->html = $this->html.'</form>'.PHP_EOL;

		//Add the textarea
		$textAreaHTML = '<textarea name="body" rows="20" cols="80" form="blogPostForm"></textarea>'.PHP_EOL;
		$this->html = $this->html.'<br>'.PHP_EOL.'<br>'.PHP_EOL.$textAreaHTML;



		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);
	}

	public function buildInputArray() {

		$titleInputElement = new FormElementInput('title', 'text', $this->formName);
		$this->inputArray['title'] = $titleInputElement;
		$titleInputElement->displayName = 'Title';
		$titleInputElement->size = 60;
		$titleInputElement->setupForm();

		// add an empty FormElementInput object to handle the textarea input
		$textAreaInputElement = new FormElementInput('body', 'hidden', $this->formName);
		$this->inputArray['body'] = $textAreaInputElement;
		$textAreaInputElement->storeRenderSignal(false);
		$textAreaInputElement->setupForm();


		$submitInput = new FormElementInput('submit', 'submit', $this->formName);
		$submitInput->formValue = $this->submitName;
		$submitInput->setupForm();
		$this->inputArray['submit'] = $submitInput;

	}

	public function responseStateHandler($responseState) {
		// If success, redirect to new blog page. Otherwise, just reload.
		if ($responseState == 1) {
			$this->send303($this->newPageURI);
		} else {
			//$this->send303();
		}
	}

	function send303($paramURI = '') {

		// By default, URI is current page.
		$uri = $_SERVER[REQUEST_URI];
		if (empty($paramURI) == false)
			$uri = $paramURI;

		$link = "http://$_SERVER[HTTP_HOST]$uri";
		header("HTTP/1.1 303 See Other");
		header('Location: '.$link);
	}

	public function elementAction() {

		$doAttemptPost = true;
		$newPageId = 0;

		if ($this->postState == false) {
			$this->generateFormHTML();
		} else {
			// Get the input data
			$titleInput = $this->inputArray['title'];
			$title = filter_var($titleInput->postData,FILTER_SANITIZE_STRING);

			$bodyInput = $this->inputArray['body'];
			$body = filter_var($bodyInput->postData,FILTER_SANITIZE_STRING);

			// Validate input data: Pass if neither is empty.
			if (empty($title) == true) {
				$titleInput->storeMessage('Please enter a title.');
				$doAttemptPost = false;
			}

			if (empty($body) == true) {
				$bodyInput->storeMessage('Please enter some text in your blog post.');
				$doAttemptPost = false;
			}

			// If there's some text in both fields, go ahead and generate a blog post.
			if ($doAttemptPost == true) {

				$queryParams = array (
						 ':pageId' => strval($this->pageViewReference->pageId)
						,':userId' => strval($this->pageViewReference->sessionManager->userId)
						,':title' => $title
						,':body' => $body
						,':pagebuilderClass' => $GLOBALS['gBlogDefaultPagebuilder']

				);

				$sql = <<<SQL
					SELECT toonces.CREATE_BLOG_POST (
						 :pageId				--   param_page_id BIGINT
						,:userId				--  ,param_user_id BIGINT
						,:title					--  ,param_title VARCHAR(200)
						,:body					--  ,param_body TEXT
						,:pagebuilderClass		--  ,param_pagebuilder_class VARCHAR(50)
						,''						--  ,param_thumbnail_image_vector VARCHAR(50)
					)
SQL;
				if (!isset($this->conn))
					$this->conn = UniversalConnect::doConnect();

				$stmt = $this->conn->prepare($sql);

				$stmt->execute($queryParams);

				$result = $stmt->fetch(PDO::FETCH_NUM);
				// Get the resulting page ID
				$this->newPageId = intval($result[0]);

			}

			// If success, store the new page's URI.

			if ($this->newPageId != 0) {
				if (!isset($this->conn))
					$this->conn = UniversalConnect::doConnect();

				$sql = 'SELECT GET_PAGE_PATH(:paramPageId)';
				$stmt = $this->conn->prepare($sql);
				$stmt->execute(array(':paramPageId' => strval($this->newPageId)));

				$result = $stmt->fetch(PDO::FETCH_NUM);
				// get the new URI
				$this->newPageURI = $result[0];

				$this->responseStateHandler(1);

			} else {
				$this->responseStateHandler(0);
			}
		}
	}

	public function objectSetup() {

		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		$this->formName = 'blogPostForm';

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