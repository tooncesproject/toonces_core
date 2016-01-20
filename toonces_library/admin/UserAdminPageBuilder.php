<?php

//include_once ROOTPATH.'/admin/AdminViewElement.php';
include_once ROOTPATH.'/admin/AdminToolBuilder.php';

class UserAdminPageBuilder extends AdminPageBuilder
{
	// Instance variables
	// Inherited variables are commented out
	//var $styleSheet;
	//var $pageTitle;
	//var $elementArray = array();
	//var $pageViewReference;
	//var $toolElement;
	//var $adminAccessOnly;
	var $conn;

	function buildAdminTool() {
				
		// Make a copy block element for the top
		$html = <<<HTML
		<div class="copy_block">
		<h2>Toonces User Administration Tools</h2>
		<p><a href="/admin/useradmin/createuser">Create New User</a>
		</div>
HTML;

	$topCopyBlock = new Element($this->pageViewReference);
	$topCopyBlock->setHTML($html);

	$this->toolElement->addElement($topCopyBlock);
	$this->toolElement->addElement($this->buildUserList());
	
	}

	function buildUserList() {

			// Query the database for a list of users
		$SQL = <<<SQL
		SELECT
			 user_id
			,email
			,nickname
			,firstname
			,lastname
			,is_admin
		FROM
			toonces.users;
SQL;
		$this->conn = UniversalConnect::doConnect();
		$result = $this->conn->query($SQL);
		
		// html template for users
		$userRowTemplate = '<tr><td><a href="/admin/useradmin/manageuser?userid=%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>'.PHP_EOL;
		$table = '<div class="copy_block"><table border = 1	><tr><td>User Id</td><td>email</td><td>nickname</td><td>first name</td><td>last name</td><td>User is admin</td>'.PHP_EOL;
		foreach($result as $row) {
			if ($row['is_admin'] == 1) {
				$isAdmin = 'Yes';
			} else {
				$isAdmin = 'No';
			}
			$userRow = sprintf($userRowTemplate, $row['user_id'],$row['user_id'],$row['email'],$row['nickname'],$row['firstname'],$row['lastname'],$isAdmin);
			$table = $table.$userRow;
		}
		$table = $table.'</table></div>';
		
		$userListElement = new Element($this->pageViewReference);
		$userListElement->setHTML($table);
		$userListElement->setHtmlHeader('<div class="copy_block">');
		$userListElement->setHtmlFooter('</div>');
		
		return $userListElement;
		
	}
}