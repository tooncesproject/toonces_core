/*************** WOO *********************

sp_delete_resource
Paul Anderson 10/4/2015

This SQL procedure permanently deletes
a resource and (recursively) all its children.

It also deletes all dependent records,
such as toonces.ext_html_page

***************** WOO ********************/

DROP PROCEDURE IF EXISTS toonces.sp_delete_resource;
--%c
DELIMITER //
--/%c
CREATE PROCEDURE toonces.sp_delete_resource(param_resource_id BIGINT)

READS SQL DATA
MODIFIES SQL DATA

BEGIN

    -- functional variables
    DECLARE var_resource_id BIGINT;
    DECLARE var_descendant_resource_id BIGINT;

    DECLARE var_loopfinished BOOL DEFAULT FALSE;
    DECLARE var_child_page_cursor CURSOR FOR 
        SELECT
             resource_id
            ,descendant_resource_id
        FROM
            toonces.resource_hierarchy_bridge
        WHERE
            resource_id = param_resource_id
    ;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET var_loopfinished = TRUE;

    SET max_sp_recursion_depth = 255;

    -- Does the resource have any children?
    -- If so, recurse the function for each of its children.
    OPEN var_child_page_cursor;

    read_loop: LOOP

        FETCH var_child_page_cursor INTO
             var_resource_id
            ,var_descendant_resource_id
        ;

        IF var_loopfinished THEN 
            LEAVE read_loop;
        END IF;

        CALL toonces.sp_delete_resource(var_descendant_resource_id);

    END LOOP;

    CLOSE var_child_page_cursor;

    -- Delete content.

    -- Hard-delete parent page_hierarchy_bridge record
    DELETE FROM
        toonces.resource_hierarchy_bridge
    WHERE
        descendant_resource_id = param_resource_id
    ;

    -- Hard-delete resource_user_access record
    DELETE FROM
        resource_user_access
    WHERE
        resource_id = param_resource_id
    ;


    -- Hard-delete the record in ext_html_page, if it exists.
    DELETE FROM
        toonces.dom_resource
    WHERE
        resource_id = param_resource_id;

    -- Clear the foreign key reference in login_attempt, if it exists.
    -- Otherwise, the delete will fail due to foreign key violation.
    UPDATE
        login_attempt
    SET
        resource_id = NULL
    WHERE
        resource_id = param_resource_id;


    -- Finally, hard-delete the page.
    DELETE FROM
        toonces.resource
    WHERE
        resource_id = param_resource_id
    ;

END
--%c
//
DELIMITER ;
--/%c
