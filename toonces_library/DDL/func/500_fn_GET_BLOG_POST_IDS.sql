/*************** WOO *********************

GET_BLOG_POST_IDS

Paul Anderson 12/6/2015

This SQL function returns a string
of blog post IDs based on the following
parameters:
  * param_blog_id
        which blog to get stuff from
  * param_items_per_page: 
        number of blog posts to grab
  * param_page:
        Determines which set of posts
        to get.

***************** WOO ********************/

DROP FUNCTION IF EXISTS GET_BLOG_POST_IDS;

DELIMITER //

CREATE FUNCTION GET_BLOG_POST_IDS (
     param_blog_id          BIGINT UNSIGNED
    ,param_items_per_page   INT UNSIGNED
    ,param_page             INT UNSIGNED
)

RETURNS VARCHAR(1000)

NOT DETERMINISTIC

BEGIN
    
    -- declare return string
    DECLARE var_id_string VARCHAR(1000);

    -- create temp table to store values

    CREATE TEMPORARY TABLE temp_all_posts_for_blog
    (
         post_ordinal BIGINT AUTO_INCREMENT NOT NULL
        ,blog_post_id BIGINT NOT NULL 
        
        ,CONSTRAINT pk_temp_all_posts_for_blog PRIMARY KEY (post_ordinal)
        ,INDEX ind_post_id (blog_post_id)
    ) ENGINE=MEMORY;

    -- Store all the blog ids in reverse chronological order

    INSERT INTO temp_all_posts_for_blog
    (
        blog_post_id
    ) (
        SELECT
            blog_post_id
        FROM
            blog_posts
        WHERE
            blog_id = param_blog_id
        ORDER BY
            created_dt DESC
    );

    -- get the IDS
    SELECT
        GROUP_CONCAT(ap.post_ordinal SEPARATOR ',')
    INTO
        var_id_string
    FROM
        temp_all_posts_for_blog ap
    JOIN
        blog_posts bp USING (blog_post_id)
    WHERE
        ap.post_ordinal BETWEEN param_items_per_page * param_page - param_items_per_page + 1 AND param_items_per_page * param_page;

    DROP TEMPORARY TABLE temp_all_posts_for_blog;

    RETURN var_id_string;

END //

DELIMITER ;
