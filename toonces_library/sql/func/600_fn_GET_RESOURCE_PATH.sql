-- GET_RESOURCE_PATH
-- Initial Commit: Paul Anderson 1/23/2016
-- Acquires a page URL path from a page ID.
DROP FUNCTION IF EXISTS toonces.GET_RESOURCE_PATH;

--%c
DELIMITER //
--/%c

CREATE FUNCTION toonces.GET_RESOURCE_PATH(param_resource_id BIGINT)

RETURNS VARCHAR(2000)

NOT DETERMINISTIC

BEGIN

    DECLARE var_page_path VARCHAR(2000) DEFAULT '';
    DECLARE var_pathname VARCHAR(100) DEFAULT '';
    DECLARE var_ancestor_resource_id BIGINT;
    DECLARE var_resource_id BIGINT;

    SET var_resource_id = param_resource_id;

    pathloop:LOOP

        -- Query pages for the page's pathname 
        SELECT
             p.pathname
            ,rhb.resource_id
        INTO
             var_pathname
            ,var_ancestor_resource_id
        FROM
            toonces.resource p
        LEFT OUTER JOIN
            toonces.resource_hierarchy_bridge rhb ON p.resource_id = rhb.descendant_resource_id
        WHERE
            p.resource_id = var_resource_id
        ;

        SET var_page_path = CONCAT(COALESCE(var_pathname,''),'/',var_page_path);

        IF var_ancestor_resource_id IS NULL THEN
            LEAVE pathloop; 
        END IF;

        SET var_resource_id = var_ancestor_resource_id;

    END LOOP;

    RETURN var_page_path;

END
--%c
//
DELIMITER ;
--/%c
