<?php
/*
 * PublishLinkControlElement
 * Initial commit: Paul Anderson, 2016-03-13
 *
 * 		If the linkaction=publishpage variable is set in the query string,
 * 		this LinkActionController publishes the page.
 *
 */

class PublishLinkControlElement extends LinkActionControlElement
{

	var $conn;

	public function linkAction() {


		// Does the user have editing rights to this page?
		$userCanEdit = $this->pageViewReference->userCanEdit;

		// If one or the other is true, go ahead and publish the page.
		if ($userCanEdit) {

			// Set connection
			if (isset($this->conn) == false) {
			    $this->conn = $this->pageViewReference->conn;

			$pageId = $this->pageViewReference->pageId;

			$sql = <<<SQL
			UPDATE toonces.pages
			SET published = TRUE
			WHERE page_id = :pageId
SQL;

			$sqlParams = array(':pageId' => $pageId);

			$stmt = $this->conn->prepare($sql);

			$stmt->execute($sqlParams);

			$this->redirectUser();

			}
		}
	}


	public function objectSetup() {
		$this->linkActionName = 'publishpage';
	}

}