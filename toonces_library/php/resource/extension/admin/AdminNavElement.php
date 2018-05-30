<?php
class AdminNavElement extends NavElement implements iResource
// Initial commit: Paul Anderson, 1/23/2016
// This Element class dynamically builds a navigation menu for 
// admin tools, including only those the user has privileges to access.
{
	
	function buildLinkArray() {

		$userIsAdmin = $this->pageViewReference->sessionManager->userIsAdmin;

		$this->userId = $this->pageViewReference->sessionManager->userId;
		if (!isset($this->userId)) {
			throw new Exception('User ID variable must be set before link array is built.');
		}

		// Query database for admin pages, admin page hierarchy and user's access.
		$SQL = <<<SQL
		SELECT
			 p.page_id
			,p.pathname
			,COALESCE(phb.page_id, 0) AS ancestor_page_id
			,COALESCE(phb2.descendant_page_id,0) AS descendant_page_id
			,p.page_link_text
			,COALESCE(pua.page_id,0) AS pua_page_id
			,GET_PAGE_PATH(p.page_id) AS path
		FROM
			toonces.pages p
		LEFT OUTER JOIN
			toonces.page_user_access pua
				ON p.page_id = pua.page_id
				AND pua.user_id = %s
		LEFT OUTER JOIN
			toonces.page_hierarchy_bridge phb
				ON p.page_id = phb.descendant_page_id
		LEFT OUTER JOIN
			toonces.page_hierarchy_bridge phb2
				ON p.page_id = phb2.page_id
		WHERE
			p.pagebuilder_class IN ('AdminHomeBuilder','UserAdminPageBuilder','CreateUserAdminPageBuilder')
		ORDER BY
			p.page_id
SQL;
		$SQL = sprintf($SQL,$this->userId);
		$this->conn = $this->pageViewReference->getSQLConn();
		$result = $this->conn->query($SQL);

		// Iterate through results.
		// Where user has access, acquire URL and add to array.
		$linkOrdinal = 0;
		foreach ($result as $row) {
			// Generate a link if:
			// User is admin OR user has access OR page is admin home.
			if ($userIsAdmin == 1 or intval($row['pua_page_id']) != 0 or $row['pathname'] == 'admin') {
				//Create a link object for each
				$dynamicLink = new DynamicNavigationLink
					(
						 $linkOrdinal
						,intval($row['page_id'])
					 	,$row['path']
					 	,$row['page_link_text']
						,intval($row['ancestor_page_id'])
						,intval($row['descendant_page_id'])
					);
				array_push($this->linkArray, $dynamicLink);

				$linkOrdinal++;
			}	
		}
	}
}