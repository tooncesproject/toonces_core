/*************** WOO *********************

Paul Anderson 10/4/2015

This SQL function generates the sql record
and root page for a new blog.


***************** WOO ********************/



DROP FUNCTION IF EXISTS toonces.CREATE_BLOG;

DELIMITER //

CREATE FUNCTION toonces.CREATE_BLOG (
	parent_page_id BIGINT
	,blog_url_name VARCHAR(50)
	,blog_display_name VARCHAR(100)
	,blog_pagebuilder_class VARCHAR(50)
	,blog_pageview_class VARCHAR(50)
	,css_stylesheet VARCHAR(100)
	
)

RETURNS BIGINT

BEGIN

	DECLARE blog_id BIGINT;
	DECLARE new_blog_page_id BIGINT;
	DECLARE test_page_id BIGINT;

	-- check to make sure page exists

	SELECT
		page_id
	FROM
		toonces.pages
	WHERE
		page_id = parent_page_id
	INTO
		test_page_id;

	IF test_page_id IS NOT NULL THEN 
		
		-- if parent page exists, create page and blog
		SELECT toonces.CREATE_PAGE(
			1							-- parent page id
			,blog_url_name				-- pathname
			,blog_display_name			-- page_title
			,blog_display_name			-- page_link_text
			,blog_pagebuilder_class		-- pagebuilder_class
			,blog_pageview_class		-- pageview_class
			,css_stylesheet				-- css_stylesheet
			,1							-- redirect on error
			,1							-- page active
			)
		INTO new_blog_page_id;

		INSERT INTO toonces.blogs (
			page_id
		)
		VALUES (
			new_blog_page_id
		
		);
		SET blog_id = last_insert_id();

	END IF;

	RETURN blog_id;

END;

