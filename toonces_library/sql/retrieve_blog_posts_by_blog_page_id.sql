SELECT
    bp.created_dt
    ,bp.author
    ,bp.title
    ,bp.body
    ,bp.page_id
FROM
    toonces.blogs blogs
JOIN
	toonces.blog_posts bp USING (blog_id)
WHERE
	blogs.page_id = %d
ORDER BY created_dt DESC
LIMIT 10
;