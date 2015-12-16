DROP PROCEDURE IF EXISTS toonces.sp_get_blog_posts;

DELIMITER //

CREATE PROCEDURE toonces.sp_get_blog_posts (
     param_blog_id          BIGINT UNSIGNED
    ,param_items_per_page   INT UNSIGNED
    ,param_page             INT UNSIGNED
)


BEGIN
    
    -- declare return string


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
            toonces.blog_posts
        WHERE
            blog_id = param_blog_id
        ORDER BY
            created_dt DESC
    );

    -- get the IDS
    SELECT
         created_dt
        ,author
        ,title
        ,body
        ,page_id
    FROM
        temp_all_posts_for_blog ap
    JOIN
        toonces.blog_posts bp USING (blog_post_id)
    WHERE
        ap.post_ordinal BETWEEN param_items_per_page * param_page - param_items_per_page + 1 AND param_items_per_page * param_page;

   -- DROP TEMPORARY TABLE temp_all_posts_for_blog;


END //

DELIMITER ;
