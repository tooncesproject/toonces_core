/*************** WOO *********************

CREATE_BLOG
Paul Anderson 10/4/2015

This SQL function generates the sql record
and root page for a new blog.


***************** WOO ********************/



DROP FUNCTION IF EXISTS CREATE_BLOG;
--%c
DELIMITER //
--/%c

CREATE FUNCTION CREATE_BLOG (
     parent_page_id BIGINT
    ,blog_url_name VARCHAR(50)
    ,blog_display_name VARCHAR(100)
    ,blog_pagebuilder_class VARCHAR(50)
    ,blog_pageview_class VARCHAR(50)
    ,css_stylesheet VARCHAR(100)
    
)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE blog_id BIGINT;
    DECLARE new_blog_page_id BIGINT;
    DECLARE test_page_id BIGINT;

    -- check to make sure page exists

    SELECT
        page_id
    FROM
        pages
    WHERE
        page_id = parent_page_id
    INTO
        test_page_id;

    IF test_page_id IS NOT NULL THEN 
        
        -- if parent page exists, create page and blog
        SELECT CREATE_PAGE(
             parent_page_id             -- parent page id
            ,blog_url_name              -- pathname
            ,blog_display_name          -- page_title
            ,blog_display_name          -- page_link_text
            ,blog_pagebuilder_class     -- pagebuilder_class
            ,blog_pageview_class        -- pageview_class
            ,css_stylesheet             -- css_stylesheet
            ,1                          -- redirect on error
            ,1                          -- published
            ,2                          -- pagetype_id - Blog root page type
            )
        INTO new_blog_page_id;

        INSERT INTO blogs (
            page_id
        )
        VALUES (
            new_blog_page_id
        
        );
        SET blog_id = last_insert_id();

    END IF;

    RETURN blog_id;

END
--%c
//
DELIMITER ;
--/%c
