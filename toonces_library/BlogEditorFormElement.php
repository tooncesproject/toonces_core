<?php
/*
 * BlogEditorFormElement
 * Initial commit: Paul Anderson 2/21/2016
 * 
 * Extends BlogFormElement for updating existing blog posts.
 * 
 */

class BlogEditorFormElement extends BlogFormElement implements iElement
{

	public $updatedBlogPostTitle;
	public $blogPostId;


	function queryBlog() {

		if (!isset($this->conn))
			$this->conn = UniversalConnect::doConnect();

		$query = sprintf(file_get_contents(ROOTPATH.'/sql/retrieve_single_blog_post.sql'),$this->pageViewReference->pageId);
		$result = $this->conn->query($query);
		return $result;

	}

	public function responseStateHandler($responseState) {

		switch ($responseState) {
			case 0: 
				// If no title change, exit edit mode.
				$path = $this->pageViewReference->urlPath;
				$this->send303($path);
				break;
			case 1:
				// If title change detected, set checkNewPageTitle session variable
				// and send a 303 header, staying in edit mode.
		//		$_SESSION['checkNewPageTitle'] = $this->updatedBlogPostTitle;
		//		$this->send303();
		//		break;
		//	case 2:
				// If user has been directed to URL check screen,
				// Redirect to the PageBuilder's URL Check mode
				$checkTitle = urlencode($this->updatedBlogPostTitle);
				$path = $this->pageViewReference->urlPath;
				$path = '/'.$path.'?mode=urlcheck&newtitle='.$checkTitle;
				$this->send303($path);
				break;
		}
	}

	public function elementAction() {

		$doAttemptPost = true;
		$newPageId = 0;
		$this->textareaValueVarName = $this->formName.'_tav';

		if ($this->postState == false) {
			// If there's no POST but the checkNewPageTitle sesh var is not set,
			// generate the form.
			// Otherwise, go to the URL management screen.
			//if (isset($_SESSION['checkNewPageTitle']) == false) {

				// Query the existing data.
				$result = $this->queryBlog();
				foreach($result as $row) {

					$this->blogPostTitle = $row['title'];
					$this->textareaValue = $row['body'];

				//}

				$this->generateFormHTML();
		/*	} else {
				// Destroy the signal
				unset($_SESSION['checkNewPageTitle']);
				// Load the URL check screen
				$this->responseStateHandler(2); */
			}
		} else {
			// If there is POST data, validate and update.

			if (!isset($this->conn))
				$this->conn = UniversalConnect::doConnect();

			// Get the input data
			$titleInput = $this->inputArray['title'];
			$title = filter_var($titleInput->postData,FILTER_SANITIZE_STRING);

			$bodyInput = $this->inputArray['body'];
			$body = filter_var($bodyInput->postData,FILTER_SANITIZE_STRING);

			// Validate input data:
			// If title is empty, default to the existing title.
			if (empty($title) == true) {
				$title = $this->blogPostTitle;
			} 

			// If body is empty, nag the user.
			if (empty($body) == true) {
				$bodyInput->storeMessage('Please enter some text in your blog post.');
				$doAttemptPost = false;
				$bodyInput->storeValue($body);
			}

			// Otherwise, go ahead and update the blog content.
			if ($doAttemptPost == true) {

				$queryParams = array (
						 ':body' => $body
						,':blogPostId' => $this->blogPostId 
				);

				$sql = <<<SQL
					UPDATE toonces.blog_posts
					SET body = :body
					WHERE blog_post_id = :blogPostId
SQL;

				$stmt = $this->conn->prepare($sql);
				$stmt->execute($queryParams);

				// If there's no change to the title, response state 0 (OK, go to post).
				// Otherwise, state 1 (Check for URL change)
				if ($title == $this->blogPostTitle) {
					$this->responseStateHandler(0);
				} else {
					$this->updatedBlogPostTitle = $title;
					$this->responseStateHandler(1);
				}

			}
		}
	}

	public function objectSetup() {

		$this->htmlHeader = '<div class="form_element>';
		$this->htmlFooter = '</div>';
		$this->formName = 'blogPostForm';

		$this->submitName = 'Save';
		// Instantiate input objects
		$this->buildInputArray();
		// Iterate through input objects to see if any received a POST
		foreach ($this->inputArray as $input) {
			if ($input->postState == true)
				$this->postState = true;

		}
	}
}