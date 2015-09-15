SELECT
	page_id,
	pathname,
	page_title,
	page_link_text,
	pagebuilder_class,
	pageview_class,
	css_stylesheet
FROM
	toonces.pages
WHERE
	page_id = %d;