/*************** WOO *********************

DELETE_PAGE
Paul Anderson 10/4/2015

This SQL procedure permanently deletes
a page and (recursively) all its children.

It also deletes all dependent records,
such as toonces.blogs or toonces.blog_post.

***************** WOO ********************/

DROP PROCEDURE IF EXISTS toonces.sp_delete_page;
-- %c
DELIMITER //
-- /%c
CREATE PROCEDURE toonces.sp_delete_page(param_page_id BIGINT)

READS SQL DATA
MODIFIES SQL DATA

BEGIN

    -- functional variables
    DECLARE var_page_id BIGINT;
    DECLARE var_descendant_page_id BIGINT;

    DECLARE var_loopfinished BOOL DEFAULT FALSE;
    DECLARE var_child_page_cursor CURSOR FOR 
        SELECT
             page_id
            ,descendant_page_id
        FROM
            toonces.page_hierarchy_bridge
        WHERE
            page_id = param_page_id
    ;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET var_loopfinished = TRUE;

    SET max_sp_recursion_depth = 255;

    -- Does the page have any children?
    -- If so, recurse the function for each of its children.
    OPEN var_child_page_cursor;

    read_loop: LOOP

        FETCH var_child_page_cursor INTO
             var_page_id
            ,var_descendant_page_id
        ;

        IF var_loopfinished THEN 
            LEAVE read_loop;
        END IF;

        CALL toonces.sp_delete_page(var_descendant_page_id);

    END LOOP;

    CLOSE var_child_page_cursor;

    -- Delete content.

    -- Hard-delete parent page_hierarchy_bridge record
    DELETE FROM
        toonces.page_hierarchy_bridge
    WHERE
        descendant_page_id = param_page_id
    ;

    -- Hard-delete page_user_access record
    DELETE FROM
        page_user_access
    WHERE
        page_id = param_page_id
    ;

    -- Hard-delete any record in blog posts, if it exists.
    DELETE FROM
        toonces.blog_posts
    WHERE
        page_id = param_page_id
    ;
    
    -- Hard-delete any record in blogs, if it exists
    DELETE FROM
        toonces.blogs
    WHERE
        page_id = param_page_id
    ;

    -- Finally, hard-delete the page.
    DELETE FROM
        toonces.pages
    WHERE
        page_id = param_page_id
    ;

END
-- %c
//
DELIMITER ;
-- /%c
