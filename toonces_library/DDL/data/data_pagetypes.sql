INSERT INTO toonces.pagetypes
(
     pagetype_id
    ,name
    ,description
    ,restricted_access
) VALUES
     (0,    'general',      'Uncategorized page, no special access restrictions',   0)
    ,(1,    'admin',        'Part of Toonces Admin tools',                          1)
    ,(2,    'blog',         'Root page of a blog',                                  0)
    ,(3,    'blog post',    'Individual post entry for a blog',                     0)
    ,(4,    'content page', 'Non-blog dynamic content page',                        0)
ON DUPLICATE KEY UPDATE
     name = VALUES (name)
    ,description = VALUES (description)
    ,restricted_access = VALUES (restricted_access)
;