-- get blog post ids by page id
SELECT
    bp.blog_post_id
FROM
    toonces.blogs blg
JOIN
    toonces.pages pgs ON blg.page_id = pgs.page_id
JOIN
    toonces.blog_posts bp ON blg.blog_id = bp.blog_id
WHERE
    pgs.page_id = %d
ORDER BY
    bp.created_dt DESC;