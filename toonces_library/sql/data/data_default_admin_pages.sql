INSERT INTO toonces.adminpages
(
     adminpage_id
    ,admin_parent_page_id
    ,pathname
    ,page_title
    ,page_link_text
    ,pagebuilder_class
    ,pageview_class
    ,css_stylesheet
    ,redirect_on_error
    ,published
) VALUES 
    -- Main admin page - 
    (1, 0, 'admin', 'Toonces Admin',    'Site Administration',   'AdminHomeBuilder', 'PageView', 'toonces_admin.css',    0,  1)
        -- User administration
        ,(2, 1, 'useradmin', 'Toonces Admin | User Administration Tools',    'User Administration Tools',   'UserAdminPageBuilder', 'PageView', 'toonces_admin.css',    0,  0)
            -- create user
            ,(3, 2, 'createuser', 'Toonces Admin | Create User',    'Create User',   'CreateUserAdminPageBuilder', 'PageView', 'toonces_admin.css',    0,  0)
            -- manage user
            ,(4, 2, 'manageuser', 'Toonces Admin | Manage User',    'Manage User',   'ManageUserAdminPageBuilder', 'PageView', 'toonces_admin.css',    0,  0)
        -- Page management
        ,(5, 1, 'pageadmin', 'Toonces Admin | Page Administration Tools',    'Page Administration Tools',   'PageAdminPageBuilder', 'PageView', 'toonces_admin.css',    0,  0)
            -- Edit Page
            ,(6, 5, 'editpage', 'Toonces Admin | Edit Page',    'Edit Page',   'EditPageAdminPageBuilder', 'PageView', 'toonces_admin.css',    0,  0)
ON DUPLICATE KEY UPDATE
     adminpage_id           = VALUES(adminpage_id)
    ,admin_parent_page_id   = VALUES(admin_parent_page_id)
    ,pathname               = VALUES(pathname)
    ,page_title             = VALUES(page_title)
    ,page_link_text         = VALUES(page_link_text)
    ,pagebuilder_class      = VALUES(pagebuilder_class)
    ,pageview_class         = VALUES(pageview_class)
    ,css_stylesheet         = VALUES(css_stylesheet)
    ,redirect_on_error      = VALUES(redirect_on_error)
    ,published              = VALUES(published)
;