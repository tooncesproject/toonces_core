/*************** WOO *********************

Paul Anderson 10/4/2015

This SQL function generates both a blog 
post and its parent "page" to publish.


***************** WOO ********************/

DROP FUNCTION IF EXISTS toonces.CREATE_BLOG_POST;

DELIMITER //


CREATE FUNCTION toonces.CREATE_BLOG_POST (
	parent_blog_id BIGINT
	,author VARCHAR(50)
	,title VARCHAR(200)
	,body TEXT
	,pagebuilder_class VARCHAR(50)
	,thumbnail_image_vector VARCHAR(50)
	
)

RETURNS BIGINT

BEGIN

	DECLARE blog_page_id BIGINT;
	DECLARE blog_post_page_id BIGINT;
	DECLARE pathname VARCHAR(50);
	-- DECLARE page_link_text VARCHAR(100);
	DECLARE post_pageview_class VARCHAR(50);
	DECLARE post_css_stylesheet VARCHAR(50);

	-- Get blog page ID
	SELECT
		page_id
	INTO 
		blog_page_id
	FROM 
		toonces.blogs
	WHERE
		blog_id = parent_blog_id;
	
	
	-- get page data

	SELECT
		pageview_class
		,css_stylesheet
	INTO 
		post_pageview_class
		,post_css_stylesheet
	FROM
		toonces.pages
	WHERE
		page_id = blog_page_id
	
	;

	-- generate pathname
	-- strip all non-alphanumeric characters, lowercase and truncate
	SET pathname = toonces.GENERATE_PATHNAME(title);
	-- generate page


	SELECT toonces.CREATE_PAGE (
		blog_page_id			-- parent_page_id BIGINT,
		,pathname				-- pathname VARCHAR(50)
		,title					-- page_title VARCHAR(50)
		,title					-- page_link_text VARCHAR(50)
		,pagebuilder_class		-- pagebuilder_class VARCHAR(50)
		,post_pageview_class	-- pageview_class VARCHAR(50)
		,post_css_stylesheet	-- css_stylesheet VARCHAR(100)
		,1						-- redirect_on_error BOOL
		,1						-- page_active BOOL
	) INTO blog_post_page_id;
		
	-- insert record into blog_posts table

	INSERT INTO toonces.blog_posts (
		blog_id
		,page_id
		,author
		,title
		,body
		,thumbnail_image_vector
	) VALUES (
		parent_blog_id
		,blog_post_page_id
		,author
		,title
		,body
		,thumbnail_image_vector
	);

	RETURN blog_post_page_id;

END;

