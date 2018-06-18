/*

    ADD A PAGE FUNCTION
    PAUL ANDERSON 9/1/2015
    BOOYA GRANDMA!

    returns the new page id if success
    returns null if failure.

*/

DROP FUNCTION IF EXISTS CREATE_RESOURCE;

--%c
DELIMITER //
--/%c

CREATE FUNCTION CREATE_RESOURCE  (
     parent_resource_id BIGINT
    ,pathname VARCHAR(50)
    ,resource_class VARCHAR(50)
    ,redirect_on_error BOOL
    ,published BOOL

)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE new_resource_id BIGINT;
    DECLARE ancestor_resource_id BIGINT;
    DECLARE existing_resource_id BIGINT;
    DECLARE pathname_exists BOOL;
    
    /* check for existing parent page id */
    
    SELECT resource_id
    INTO existing_resource_id
    FROM resource
    WHERE resource_id = parent_resource_id;

    /* check for existing page with same pathname with same parent page */

    SELECT
        CASE
            WHEN count(*) = 0 THEN 0
            WHEN count(*) > 0 THEN 1
        END
    INTO pathname_exists
    FROM resource_hierarchy_bridge rhb
    JOIN resource tp on tp.resource_id = rhb.descendant_resource_id
    WHERE
        rhb.resource_id = parent_resource_id
    AND 
        tp.pathname = pathname;


    /* if all is well, proceed. */  
    IF 
        existing_resource_id IS NOT NULL
    AND
        pathname_exists = 0
    THEN

        /* if parent page id is not the homepage, query for parent's ancestor.
            otherwise, leave ancestor ID null.. */
        IF parent_resource_id > 1 THEN
            SELECT resource_id
            INTO ancestor_resource_id
            FROM resource_hierarchy_bridge
            WHERE descendant_resource_id = parent_resource_id;
        END IF;

        -- get next page's autoincrement
        SELECT
            AUTO_INCREMENT
        INTO
            new_resource_id
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            TABLE_NAME = 'resource'
        AND
            table_schema = DATABASE()
        ;

        INSERT INTO resource (
             pathname
            ,resource_class
            ,redirect_on_error
            ,published
        ) VALUES (
             pathname
            ,resource_class
            ,redirect_on_error
            ,published
        );   
        
        INSERT INTO resource_hierarchy_bridge (
            resource_id
            , ancestor_resource_id
            , descendant_resource_id
        ) VALUES (
            parent_resource_id
            , ancestor_resource_id
            , new_resource_id
        );

    END IF;

    RETURN new_resource_id;

END
--%c
//
DELIMITER ;
--/%c
