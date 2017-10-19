-- GET_PAGE_PATH
-- Initial Commit: Paul Anderson 1/23/2016
-- Acquires a page URL path from a page ID.
DROP FUNCTION IF EXISTS toonces.GET_PAGE_PATH;

--%c
DELIMITER //
--/%c

CREATE FUNCTION toonces.GET_PAGE_PATH(param_page_id BIGINT)

RETURNS VARCHAR(2000)

NOT DETERMINISTIC

BEGIN

    DECLARE var_page_path VARCHAR(2000) DEFAULT '';
    DECLARE var_pathname VARCHAR(100) DEFAULT '';
    DECLARE var_ancestor_page_id BIGINT;
    DECLARE var_page_id BIGINT;

    SET var_page_id = param_page_id;   

    pathloop:LOOP

        -- Query pages for the page's pathname 
        SELECT
             p.pathname
            ,phb.page_id
        INTO
             var_pathname
            ,var_ancestor_page_id
        FROM
            toonces.pages p
        LEFT OUTER JOIN
            toonces.page_hierarchy_bridge phb ON p.page_id = phb.descendant_page_id
        WHERE
            p.page_id = var_page_id
        ;

        SET var_page_path = CONCAT(COALESCE(var_pathname,''),'/',var_page_path);

        IF var_ancestor_page_id IS NULL THEN 
            LEAVE pathloop; 
        END IF;

        SET var_page_id = var_ancestor_page_id;

    END LOOP;

    RETURN var_page_path;

END
--%c
//
DELIMITER ;
--/%c
