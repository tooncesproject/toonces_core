SELECT
    created_dt,
    author,
    title,
    body
FROM
    toonces.blog_posts bp
WHERE
	bp.blog_id = %d
ORDER BY created_dt DESC;
LIMIT 10;