

/*************** WOO *********************

Paul Anderson 10/4/2015

This SQL function generates both a blog 
post and its parent "page" to publish.


***************** WOO ********************/

DROP FUNCTION IF EXISTS CREATE_BLOG_POST;

DELIMITER //

CREATE FUNCTION CREATE_BLOG_POST (
     param_page_id BIGINT
    ,param_user_id BIGINT
    ,param_title VARCHAR(200)
    ,param_body TEXT
    ,param_pagebuilder_class VARCHAR(50)
    ,param_thumbnail_image_vector VARCHAR(50)
)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE var_parent_blog_id BIGINT;
    DECLARE var_blog_post_page_id BIGINT;
    DECLARE var_pathname VARCHAR(50);
    DECLARE var_post_pageview_class VARCHAR(50);
    DECLARE var_post_css_stylesheet VARCHAR(50);
    DECLARE var_user_can_edit BOOL;

    -- Get blog ID
    SELECT
        blog_id
    INTO 
        var_parent_blog_id
    FROM 
        blogs
    WHERE
        page_id = param_page_id;

    -- Check for user existence
    SELECT
        1
    INTO
        var_user_can_edit
    FROM
        users tu
    WHERE
        tu.user_id = param_user_id
    LIMIT 1;

    
    -- if blog page doesn't exist or user doesn't exist and have editing privileges, return NULL. Otherwise, proceed.
    IF var_parent_blog_id IS NOT NULL AND var_user_can_edit = 1 THEN

        -- get page data
        SELECT
             pageview_class
            ,css_stylesheet
        INTO 
             var_post_pageview_class
            ,var_post_css_stylesheet
        FROM
            pages
        WHERE
            page_id = param_page_id  
        ;

        -- generate pathname
        -- strip all non-alphanumeric characters, lowercase and truncate
        SET var_pathname = GENERATE_PATHNAME(param_title);

        -- generate page
        SELECT CREATE_PAGE (
             param_page_id           -- parent_page_id BIGINT,
            ,var_pathname               -- pathname VARCHAR(50)
            ,param_title                  -- page_title VARCHAR(50)
            ,param_title                  -- page_link_text VARCHAR(50)
            ,param_pagebuilder_class      -- pagebuilder_class VARCHAR(50)
            ,var_post_pageview_class    -- pageview_class VARCHAR(50)
            ,var_post_css_stylesheet    -- css_stylesheet VARCHAR(100)
            ,1                      -- redirect_on_error BOOL
            ,0                      -- published BOOL - Blog posts are unpublished by default
            ,3                      -- pagetype_id - Type for blog post page
        ) INTO var_blog_post_page_id;
        
        -- if page creation was sucessful, proceed.
        
        IF var_blog_post_page_id IS NOT NULL THEN
            -- insert record into blog_posts table
            INSERT INTO blog_posts (
                 blog_id
                ,page_id
                ,user_id
                ,title
                ,body
                ,thumbnail_image_vector
                ,published
            ) VALUES (
                 var_parent_blog_id
                ,var_blog_post_page_id
                ,param_user_id
                ,param_title
                ,param_body
                ,param_thumbnail_image_vector
                ,1
            );

            -- Add a page_user_access record
            INSERT INTO page_user_access (
                 page_id
                ,user_id
                ,can_edit
            ) VALUES (
                 var_blog_post_page_id
                ,param_user_id
                ,1
            );

        END IF;

    END IF;

    RETURN var_blog_post_page_id;

END //

DELIMITER ;
