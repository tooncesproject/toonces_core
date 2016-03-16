SELECT
     bp.created_dt
    ,u.nickname AS author
    ,bp.title
    ,bp.body
    ,bp.page_id
    ,bp.blog_post_id
    ,bp.blog_id
FROM
    toonces.blog_posts bp
JOIN
    toonces.users u USING (user_id)
WHERE
    bp.page_id = %d
    ;