/*

    ADD A PAGE FUNCTION
    PAUL ANDERSON 9/1/2015
    BOOYA GRANDMA!

    returns the new page id if success
    returns null if failure.

*/

DROP FUNCTION IF EXISTS CREATE_PAGE;

--%c
DELIMITER //
--/%c

CREATE FUNCTION CREATE_PAGE  (
     parent_page_id BIGINT
    ,pathname VARCHAR(50)
    ,page_title VARCHAR(50)
    ,page_link_text VARCHAR(50)
    ,pagebuilder_class VARCHAR(50)
    ,pageview_class VARCHAR(50)
    ,css_stylesheet VARCHAR(100)
    ,redirect_on_error BOOL
    ,published BOOL
    ,pagetype_id BIGINT

)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE new_page_id BIGINT;
    DECLARE ancestor_page_id BIGINT;
    DECLARE existing_page_id BIGINT;
    DECLARE pathname_exists BOOL;
    
    /* check for existing parent page id */
    
    SELECT page_id
    INTO existing_page_id
    FROM pages
    WHERE page_id = parent_page_id;

    /* check for existing page with same pathname with same parent page */

    SELECT
        CASE
            WHEN count(*) = 0 THEN 0
            WHEN count(*) > 0 THEN 1
        END
    INTO pathname_exists
    FROM page_hierarchy_bridge phb
    JOIN pages tp on tp.page_id = phb.descendant_page_id
    WHERE
        phb.page_id = parent_page_id
    AND 
        tp.pathname = pathname;


    /* if all is well, proceed. */  
    IF 
        existing_page_id IS NOT NULL
    AND
        pathname_exists = 0
    THEN

        /* if parent page id is not the homepage, query for parent's ancestor.
            otherwise, leave ancestor ID null.. */
        IF parent_page_id > 1 THEN
            SELECT page_id
            INTO ancestor_page_id
            FROM page_hierarchy_bridge
            WHERE descendant_page_id = parent_page_id;
        -- ELSE
            -- set ancestor_page_id = 1;
        END IF;

        -- get next page's autoincrement
        SELECT
            AUTO_INCREMENT
        INTO
            new_page_id
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            TABLE_NAME = 'pages'
        AND
            table_schema = DATABASE()
        ;

        INSERT INTO pages (
             pathname
            ,page_title
            ,page_link_text
            ,pagebuilder_class
            ,pageview_class
            ,css_stylesheet
            ,redirect_on_error
            ,published
            ,pagetype_id
        ) VALUES (
            pathname
            ,page_title
            ,page_link_text
            ,pagebuilder_class
            ,pageview_class
            ,css_stylesheet
            ,redirect_on_error
            ,published
            ,pagetype_id
        );   
        
        INSERT INTO page_hierarchy_bridge (
             page_id
            ,ancestor_page_id
            ,descendant_page_id
        ) VALUES (
             parent_page_id
            ,ancestor_page_id
            ,new_page_id
        );

    END IF;

    RETURN new_page_id;

END
--%c
//
DELIMITER ;
--/%c
