SELECT
	page_id,
	pathname,
	page_title,
	pagebuilder_class,
	pageview_class,
	hierarchy_level,
	css_stylesheet
FROM
	toonces.pages
WHERE
	pathname = '%s'
AND
	hierarchy_level = %d
LIMIT 1;