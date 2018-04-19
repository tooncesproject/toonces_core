DROP PROCEDURE IF EXISTS sp_create_admin_pages;

--%c
DELIMITER //
--/%c

CREATE PROCEDURE sp_create_admin_pages(param_repairpages BOOL)

READS SQL DATA
MODIFIES SQL DATA

BEGIN
    
        -- Declare variables for cursor select
    DECLARE var_adminpage_id BIGINT;
    DECLARE var_admin_parent_page_id BIGINT;
    DECLARE var_pathname VARCHAR(50);
    DECLARE var_page_title VARCHAR(100);
    DECLARE var_page_link_text VARCHAR(100);
    DECLARE var_pagebuilder_class VARCHAR(50);
    DECLARE var_pageview_class VARCHAR(50);
    DECLARE var_redirect_on_error BOOL;
    DECLARE var_published BOOL;

    -- functional variables
    DECLARE var_parent_page_id BIGINT;
    DECLARE var_existing_admin_page_id BIGINT;
    DECLARE var_new_admin_page_id BIGINT;

    DECLARE loopfinished BOOL DEFAULT FALSE;

    DECLARE pagecursor CURSOR FOR (
        SELECT
             adminpage_id
            ,admin_parent_page_id
            ,pathname
            ,page_title
            ,page_link_text
            ,pagebuilder_class
            ,pageview_class
            ,redirect_on_error
            ,published
        FROM
            adminpages
    );
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET loopfinished = TRUE;

    -- Create a temporary table storing the values of each new page ID as it's created
    DROP TEMPORARY TABLE IF EXISTS tmp_new_page_ids;
    CREATE TEMPORARY TABLE tmp_new_page_ids
    (
         adminpage_id BIGINT
        ,page_id BIGINT
            ,PRIMARY KEY (adminpage_id, page_id)
    );


    OPEN pagecursor;

    read_loop: LOOP

        -- Fetch values
        FETCH pagecursor INTO
             var_adminpage_id
            ,var_admin_parent_page_id
            ,var_pathname
            ,var_page_title
            ,var_page_link_text
            ,var_pagebuilder_class
            ,var_pageview_class
            ,var_redirect_on_error
            ,var_published
        ;
        IF loopfinished THEN
            LEAVE read_loop;
        END IF;

        SET var_existing_admin_page_id = NULL;

        -- If it's the main admin page, set the parent page ID to 1.
        -- Otherwise, get its parent page from the temp table.
        -- DEVELOPER NOTE: The parent page of any admin page MUST be created first,
        -- otherwise it won't work!
        IF var_admin_parent_page_id = 0 THEN
            SET var_parent_page_id = 1;
        ELSE
            SELECT
                page_id
            INTO
                var_parent_page_id
            FROM
                tmp_new_page_ids
            WHERE
                adminpage_id = var_admin_parent_page_id
            ;
        END IF;

        -- Check to see if the page already exists.
        -- If no, create the page.
        -- If the Repair Page parameter is set to TRUE, delete the page if it exists.
        -- Otherwise, get its page ID, create a record in tmp_new_page_ids and skip.

        -- Because this select may return NULL, it's in its on BEGIN block
        -- with its own dummy continue handler, so it doesn't break the loop.
        BEGIN
            DECLARE foo VARCHAR(3);
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET FOO = 'foo';
            
            SELECT
                page_id
            INTO
                var_existing_admin_page_id
            FROM
                toonces.pages
            WHERE
                pagetype_id = 1
            AND
                pathname = var_pathname;
        END;

        IF (var_existing_admin_page_id IS NOT NULL AND param_repairpages = TRUE) THEN
            CALL toonces.sp_delete_page(var_existing_admin_page_id);
        END IF;
        
        IF (var_existing_admin_page_id IS NOT NULL AND param_repairpages = FALSE) THEN
            SET var_new_admin_page_id = var_existing_admin_page_id;
        END IF;

        IF (var_existing_admin_page_id IS NULL OR param_repairpages = TRUE) THEN
            SELECT toonces.CREATE_PAGE
            (
                 var_parent_page_id     -- parent_page_id BIGINT
                ,var_pathname           -- pathname VARCHAR(50)
                ,var_page_title         -- page_title VARCHAR(50)
                ,var_page_link_text     -- page_link_text VARCHAR(50)
                ,var_pagebuilder_class  -- pagebuilder_class VARCHAR(50)
                ,var_pageview_class     -- pageview_class VARCHAR(50)
                ,var_redirect_on_error  -- redirect_on_error BOOL
                ,var_published          -- published BOOL
                ,1                      -- pagetype_id BIGINT - type for admin page (1)
            ) INTO var_new_admin_page_id;

        END IF;

            -- Insert a record into the temp table.
            INSERT INTO tmp_new_page_ids
            (
                 adminpage_id
                ,page_id
            ) VALUES (
                 var_adminpage_id
                ,var_new_admin_page_id
            );

    END LOOP;

    CLOSE pagecursor;

END
--%c
//
DELIMITER ;
--/%c
