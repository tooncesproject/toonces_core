-- get blog post ids
-- called by PHP classes to acquire a chroological list of blog post ids

SELECT
    blog_post_id
FROM
    toonces.blog_posts
WHERE
    blog_id IN (%s)
ORDER BY
    created_dt DESC