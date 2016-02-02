SELECT
     p.page_id
    ,p.pathname
    ,p.page_title
    ,p.page_link_text
    ,p.pagebuilder_class
    ,p.pageview_class
    ,p.css_stylesheet
    ,p.published
    ,p.pagetype_id
    ,CASE 
        WHEN pua.page_id IS NOT NULL THEN 1
        ELSE 0
    END AS user_has_access
    ,COALESCE(pua.can_edit,0) AS can_edit
FROM
    toonces.pages p
LEFT OUTER JOIN
    toonces.page_user_access pua
        ON p.page_id = pua.page_id
        AND pua.user_id = %d
WHERE
    p.page_id = %d;