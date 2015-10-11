SELECT
    created_dt
    ,author
    ,title
    ,body
    ,page_id
FROM
    toonces.blog_posts bp
WHERE
	bp.page_id = %d
	;