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
	public $textareaValue;
	public $textareaValueVarName;
	public $blogPostTitle;

	public function checkNameExistence($paramName) {

		$queryParams = array (
				':parentPageId' => strval($this->pageViewReference->pageId)
				,':title' => $paramName
		);

		$sql = <<<SQL
					SELECT
						CASE
							WHEN count(*) = 0 THEN 0
							WHEN count(*) > 0 THEN 1
						END
					FROM toonces.page_hierarchy_bridge phb
					JOIN toonces.pages tp on tp.page_id = phb.descendant_page_id
					WHERE
						phb.page_id = :parentPageId
					AND
						tp.pathname = GENERATE_PATHNAME(:title);
					AND
						tp.page_id != :parentPageId
SQL;
		$stmt = $this->conn->prepare($sql);
		$stmt->execute($queryParams);
		$result = $stmt->fetch(PDO::FETCH_NUM);
		$pageNameExists = intval($result[0]);

		return $pageNameExists;

	}

	public function generateFormHTML() {
		// Overridden in this case so we can add our textarea
		// Iterate through input objects to generate HTML.

		$formNameHTML = '';
		$messageHTML = '';
		$valueHTML = '';

		if (isset($this->formName) == false)
			throw new Exception('Form name must be set.');

		$this->messageVarName = $this->formName.'_msg';
		if (isset($_SESSION[$this->messageVarName]))
			$messageHTML = '<div class="form_message_notification"><p>'.$_SESSION[$this->messageVarName].'</p></div>';

		// Text area value. Priority goes to session data (i.e., user has failed blog post attempt but
		// we don't want to be mean and delete all they've written).
		// Next priority goes to $textAreaValue variable (i.e., user editing existing blog post).
		$this->textareaValueVarName = $this->formName.'_tav';
		if (isset($_SESSION[$this->textareaValueVarName])) {
			$valueHTML = $_SESSION[$this->textareaValueVarName].PHP_EOL;
		} else if (isset($this->textareaValue)) {
			$valueHTML = $this->textareaValue;
		}

		// Title value:
		if (isset($this->blogPostTitle)) {
			$this->inputArray['title']->formValue = $this->blogPostTitle;
			$this->inputArray['title']->setupForm();
		}

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
		$textAreaHTML = '<textarea name="body" rows="20" cols="80" form="blogPostForm">'.PHP_EOL.$valueHTML.'</textarea>'.PHP_EOL;
		$this->html = $this->html.'<br>'.PHP_EOL.'<br>'.PHP_EOL.$textAreaHTML;

		// Destroy the message session variable so it doesn't show when it's not supposed to.
		unset($_SESSION[$this->messageVarName]);

		// Destroy the text area value session variable.
		unset($_SESSION[$this->textareaValueVarName]);
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
			$this->send303();
		}
	}

	public function elementAction() {

		$doAttemptPost = true;
		$newPageId = 0;
		$this->textareaValueVarName = $this->formName.'_tav';

		if ($this->postState == false) {
			$this->generateFormHTML();
		} else {

			if (!isset($this->conn))
				$this->conn = UniversalConnect::doConnect();

			// Get the input data
			$titleInput = $this->inputArray['title'];
			$title = filter_var($titleInput->postData,FILTER_SANITIZE_STRING);
			$title = htmlspecialchars_decode($title, ENT_QUOTES);

			$bodyInput = $this->inputArray['body'];
			$body = $bodyInput->postData;
			$body = htmlspecialchars_decode($body, ENT_QUOTES);

			// Validate input data: Pass if neither is empty and if the name doesn't exist.
			if (empty($title) == true) {
				$titleInput->storeMessage('Please enter a title.');
				$doAttemptPost = false;

				$_SESSION[$this->textareaValueVarName] = $body;

			} else {

				// check name existence
				$pageNameExists = $this->checkNameExistence($title);

				if ($pageNameExists == 1) {
					$titleInput->storeMessage('Sorry, that title is already taken. Please try another.');
					$doAttemptPost = false;
					$_SESSION[$this->textareaValueVarName] = $body;
				}

			}

			if (empty($body) == true) {
				$bodyInput->storeMessage('Please enter some text in your blog post.');
				$doAttemptPost = false;
				$bodyInput->storeValue($body);
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

		$this->htmlHeader = '<div class="copy_block">';
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
