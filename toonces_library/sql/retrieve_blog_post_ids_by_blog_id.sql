-- get blog post ids by page id
SELECT
    blog_post_id
FROM
    toonces.blog_posts
WHERE
    blog_id IN (%s)
ORDER BY
    created_dt DESC;