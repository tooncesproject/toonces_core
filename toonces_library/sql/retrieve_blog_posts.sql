SELECT
     created_dt
    ,author
    ,title
    ,body
    ,page_id
FROM
    toonces.blog_posts bp
WHERE
	bp.blog_post_id IN (%s)
ORDER BY
	created_dt DESC;