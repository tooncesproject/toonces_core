<?php
/*
 * UnPublishLinkControlResource
 * Initial commit: Paul Anderson, 2016-03-13
 *
 * 		If the linkaction=publishpage variable is set in the query string,
 * 		this LinkActionController publishes the page.
 *
 */

class UnPublishLinkControlResource extends LinkActionControlResource
{

	var $conn;

	public function linkAction() {

		// This function is only called if the page is published.

		// Does the user have editing rights to this page?
	    $userCanEdit = $this->pageViewReference->checkUserCanEdit();

		// Is the user admin?
		$userIsAdmin = $this->pageViewReference->userIsAdmin;

		// If one or the other is true, go ahead and publish the page.
		if ($userCanEdit or $userIsAdmin) {

			// Set connection
			if (isset($this->conn) == false) {
			    $this->conn = $this->pageViewReference->getSQLConn();

			$pageId = $this->pageViewReference->pageId;

			$sql = <<<SQL
			UPDATE toonces.pages
			SET published = FALSE
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
		$this->linkActionName = 'unpublishpage';
	}

}