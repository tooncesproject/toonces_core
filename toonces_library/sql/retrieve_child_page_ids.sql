SELECT
	phb.descendant_page_id,
	pg.pathname
FROM
	toonces.page_hierarchy_bridge phb
LEFT JOIN
	toonces.pages pg on pg.page_id = phb.descendant_page_id
WHERE
	phb.page_id = %d