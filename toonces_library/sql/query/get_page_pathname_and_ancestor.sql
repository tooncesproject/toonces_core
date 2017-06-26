/*****************************************************
	get_page_pathname_and_ancestor.sql
	Paul Anderson 10/9/2015
	
	Used bh the static GetURL method of the
	GrabPageURL PHP class.

	For the input page id (see token), acquires its
	pathname and ancestor page id. If there is no
	ancestor page, it returns an empty set.

******************************************************/
SELECT
	pg.pathname
	,phb.page_id AS ancestor_page_id
FROM
	toonces.pages pg
JOIN
	toonces.page_hierarchy_bridge phb ON pg.page_id = phb.descendant_page_id
WHERE
	pg.page_id = %d;
