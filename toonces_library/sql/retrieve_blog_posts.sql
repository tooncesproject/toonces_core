SELECT
    created_dt,
    author,
    title,
    body
FROM
    toonces.blog_posts
ORDER BY created_dt DESC;
LIMIT 10;