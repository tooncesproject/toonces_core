
DROP DATABASE IF EXISTS toonces;
CREATE DATABASE toonces;

/*********** Functions!!! *************

    FUNC YEAH

************ Functions!!! *************/

-- GENERATE_PATHNAME
-- creates a URL name based on a page name.
-- Makes it all lowercase and free of funky characters.
DROP FUNCTION IF EXISTS toonces.GENERATE_PATHNAME; 

DELIMITER // 

CREATE FUNCTION toonces.GENERATE_PATHNAME ( str VARCHAR(100) ) RETURNS VARCHAR(50)

DETERMINISTIC

BEGIN
 
    DECLARE i, len SMALLINT DEFAULT 1;
    DECLARE ret VARCHAR(50) DEFAULT '';
    DECLARE c CHAR(1);
    SET len = CHAR_LENGTH( str );

    REPEAT 
    BEGIN 
        SET c = MID( str, i, 1 );
        IF c = ' ' THEN
            SET ret = CONCAT(ret,'_');
        ELSE 
            IF c REGEXP '[[:alnum:]]' THEN 
                SET ret = CONCAT(ret,c); 
            END IF; 
        END IF;
        SET i = i + 1;
    END; 
    UNTIL i > len END REPEAT;

    -- truncate at 50 chars
    SET ret = LEFT(ret, 50);

    -- lowercase it
    SET ret = lcase(ret);
  RETURN ret; 
END // 
DELIMITER ; 


/*

    TOONCES ADD A PAGE FUNCTION
    PAUL ANDERSON 9/1/2015
    BOOYA GRANDMA!

    returns the new page id if success
    returns null if failure.

*/

DROP FUNCTION IF EXISTS toonces.CREATE_PAGE;
DELIMITER //

CREATE FUNCTION toonces.CREATE_PAGE  (
     parent_page_id BIGINT
    ,pathname VARCHAR(50)
    ,page_title VARCHAR(50)
    ,page_link_text VARCHAR(50)
    ,pagebuilder_class VARCHAR(50)
    ,pageview_class VARCHAR(50)
    ,css_stylesheet VARCHAR(100)
    ,redirect_on_error BOOL
    ,published BOOL

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
    FROM toonces.pages
    WHERE page_id = parent_page_id;

    /* check for existing page with same pathname with same parent page */

    SELECT
        CASE
            WHEN count(*) = 0 THEN 0
            WHEN count(*) > 0 THEN 1
        END
    INTO pathname_exists
    FROM toonces.page_hierarchy_bridge phb
    JOIN toonces.pages tp on tp.page_id = phb.descendant_page_id
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
            otherwise, set ancestor to O. */
        IF parent_page_id > 1 THEN
            SELECT page_id
            INTO ancestor_page_id
            FROM toonces.page_hierarchy_bridge
            WHERE descendant_page_id = parent_page_id;
        ELSE
            set ancestor_page_id = 0;
        END IF;

        INSERT INTO toonces.pages (
             pathname
            ,page_title
            ,page_link_text
            ,pagebuilder_class
            ,pageview_class
            ,css_stylesheet
            ,redirect_on_error
            ,published
        ) VALUES (
            pathname
            ,page_title
            ,page_link_text
            ,pagebuilder_class
            ,pageview_class
            ,css_stylesheet
            ,redirect_on_error
            ,published
        );
    
        SET new_page_id = last_insert_id(); 

        INSERT INTO toonces.page_hierarchy_bridge (
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

END //
DELIMITER ;

/*************** WOO *********************

CREATE_BLOG
Paul Anderson 10/4/2015

This SQL function generates the sql record
and root page for a new blog.


***************** WOO ********************/



DROP FUNCTION IF EXISTS toonces.CREATE_BLOG;

DELIMITER //

CREATE FUNCTION toonces.CREATE_BLOG (
     parent_page_id BIGINT
    ,blog_url_name VARCHAR(50)
    ,blog_display_name VARCHAR(100)
    ,blog_pagebuilder_class VARCHAR(50)
    ,blog_pageview_class VARCHAR(50)
    ,css_stylesheet VARCHAR(100)
    
)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE blog_id BIGINT;
    DECLARE new_blog_page_id BIGINT;
    DECLARE test_page_id BIGINT;

    -- check to make sure page exists

    SELECT
        page_id
    FROM
        toonces.pages
    WHERE
        page_id = parent_page_id
    INTO
        test_page_id;

    IF test_page_id IS NOT NULL THEN 
        
        -- if parent page exists, create page and blog
        SELECT toonces.CREATE_PAGE(
             parent_page_id             -- parent page id
            ,blog_url_name              -- pathname
            ,blog_display_name          -- page_title
            ,blog_display_name          -- page_link_text
            ,blog_pagebuilder_class     -- pagebuilder_class
            ,blog_pageview_class        -- pageview_class
            ,css_stylesheet             -- css_stylesheet
            ,1                          -- redirect on error
            ,1                          -- published
            )
        INTO new_blog_page_id;

        INSERT INTO toonces.blogs (
            page_id
        )
        VALUES (
            new_blog_page_id
        
        );
        SET blog_id = last_insert_id();

    END IF;

    RETURN blog_id;

END //

DELIMITER ;


/*************** WOO *********************

Paul Anderson 10/4/2015

This SQL function generates both a blog 
post and its parent "page" to publish.


***************** WOO ********************/

DROP FUNCTION IF EXISTS toonces.CREATE_BLOG_POST;

DELIMITER //

CREATE FUNCTION toonces.CREATE_BLOG_POST (
     parent_blog_id BIGINT
    ,author VARCHAR(50)
    ,title VARCHAR(200)
    ,body TEXT
    ,pagebuilder_class VARCHAR(50)
    ,thumbnail_image_vector VARCHAR(50)
)

RETURNS BIGINT

NOT DETERMINISTIC

BEGIN

    DECLARE blog_page_id BIGINT;
    DECLARE blog_post_page_id BIGINT;
    DECLARE pathname VARCHAR(50);
    -- DECLARE page_link_text VARCHAR(100);
    DECLARE post_pageview_class VARCHAR(50);
    DECLARE post_css_stylesheet VARCHAR(50);

    -- Get blog page ID
    SELECT
        page_id
    INTO 
        blog_page_id
    FROM 
        toonces.blogs
    WHERE
        blog_id = parent_blog_id;
    
    -- if blog page doesn't exist, return NULL. Otherwise, proceed.
    IF blog_page_id IS NOT NULL THEN

        -- get page data
        SELECT
             pageview_class
            ,css_stylesheet
        INTO 
             post_pageview_class
            ,post_css_stylesheet
        FROM
            toonces.pages
        WHERE
            page_id = blog_page_id  
        ;

        -- generate pathname
        -- strip all non-alphanumeric characters, lowercase and truncate
        SET pathname = toonces.GENERATE_PATHNAME(title);

        -- generate page
        SELECT toonces.CREATE_PAGE (
             blog_page_id           -- parent_page_id BIGINT,
            ,pathname               -- pathname VARCHAR(50)
            ,title                  -- page_title VARCHAR(50)
            ,title                  -- page_link_text VARCHAR(50)
            ,pagebuilder_class      -- pagebuilder_class VARCHAR(50)
            ,post_pageview_class    -- pageview_class VARCHAR(50)
            ,post_css_stylesheet    -- css_stylesheet VARCHAR(100)
            ,1                      -- redirect_on_error BOOL
            ,1                      -- published BOOL
        ) INTO blog_post_page_id;
        
        -- if page creation was sucessful, proceed.
        
        IF blog_post_page_id IS NOT NULL THEN
            -- insert record into blog_posts table
            INSERT INTO toonces.blog_posts (
                 blog_id
                ,page_id
                ,author
                ,title
                ,body
                ,thumbnail_image_vector
                ,published
            ) VALUES (
                 parent_blog_id
                ,blog_post_page_id
                ,author
                ,title
                ,body
                ,thumbnail_image_vector
                ,1
            );
        END IF;

    END IF;

    RETURN blog_post_page_id;

END //

DELIMITER ;


/*************** WOO *********************

GET_BLOG_POST_IDS

Paul Anderson 12/6/2015

This SQL function returns a string
of blog post IDs based on the following
parameters:
  * param_blog_id
        which blog to get stuff from
  * param_items_per_page: 
        number of blog posts to grab
  * param_page:
        Determines which set of posts
        to get.

***************** WOO ********************/

DROP FUNCTION IF EXISTS toonces.GET_BLOG_POST_IDS;

DELIMITER //

CREATE FUNCTION toonces.GET_BLOG_POST_IDS (
     param_blog_id          BIGINT UNSIGNED
    ,param_items_per_page   INT UNSIGNED
    ,param_page             INT UNSIGNED
)

RETURNS VARCHAR(1000)

NOT DETERMINISTIC

BEGIN
    
    -- declare return string
    DECLARE var_id_string VARCHAR(1000);

    -- create temp table to store values

    CREATE TEMPORARY TABLE temp_all_posts_for_blog
    (
         post_ordinal BIGINT AUTO_INCREMENT NOT NULL
        ,blog_post_id BIGINT NOT NULL 
        
        ,CONSTRAINT pk_temp_all_posts_for_blog PRIMARY KEY (post_ordinal)
        ,INDEX ind_post_id (blog_post_id)
    ) ENGINE=MEMORY;

    -- Store all the blog ids in reverse chronological order

    INSERT INTO temp_all_posts_for_blog
    (
        blog_post_id
    ) (
        SELECT
            blog_post_id
        FROM
            toonces.blog_posts
        WHERE
            blog_id = param_blog_id
        ORDER BY
            created_dt DESC
    );

    -- get the IDS
    SELECT
        GROUP_CONCAT(ap.post_ordinal SEPARATOR ',')
    INTO
        var_id_string
    FROM
        temp_all_posts_for_blog ap
    JOIN
        toonces.blog_posts bp USING (blog_post_id)
    WHERE
        ap.post_ordinal BETWEEN param_items_per_page * param_page - param_items_per_page + 1 AND param_items_per_page * param_page;

    DROP TEMPORARY TABLE temp_all_posts_for_blog;

    RETURN var_id_string;

END //

DELIMITER ;

/*************************** TABLES TABLES TABLES ********************************/
/*************************** TABLES TABLES TABLES ********************************/
/*************************** TABLES TABLES TABLES ********************************/
/*************************** TABLES TABLES TABLES ********************************/
/*************************** TABLES TABLES TABLES ********************************/
/*************************** TABLES TABLES TABLES ********************************/

/*************** Content Stuff ******************/

DROP TABLE IF EXISTS toonces.blog_posts;

CREATE TABLE toonces.blog_posts (
     blog_post_id BIGINT NOT NULL AUTO_INCREMENT
    ,blog_id BIGINT NOT NULL
    ,page_id BIGINT NOT NULL
    ,created_dt TIMESTAMP NOT NULL
    ,modified_dt DATETIME
    ,deleted TIMESTAMP NULL
    ,created_by VARCHAR(50)
    ,author VARCHAR(50)
    ,title VARCHAR(200)
    ,body TEXT
    ,thumbnail_image_vector VARCHAR(50)
    ,published BOOL,

    PRIMARY KEY (blog_post_id)
    
);

/* commented out, not compatble with MySQL 5.5 or older
ALTER TABLE toonces.blog_posts 
    MODIFY modified_dt datetime DEFAULT CURRENT_TIMESTAMP;
*/

DROP TABLE IF EXISTS toonces.pages;

CREATE TABLE toonces.pages (
     page_id BIGINT NOT NULL auto_increment
    ,pathname VARCHAR(50)
    ,page_title VARCHAR(100)
    ,page_link_text VARCHAR(100)
    ,pagebuilder_class VARCHAR(50) NOT NULL
    ,pageview_class VARCHAR(50) NOT NULL
    ,css_stylesheet VARCHAR(100) NOT NULL
    ,created_by VARCHAR(50)
    ,created_dt TIMESTAMP NOT NULL
    ,modified_dt DATETIME
    ,redirect_on_error BOOL
    ,published BOOL
    ,is_admin_page BOOL

    PRIMARY KEY (page_id)
);

/* commented out, not compatble with MySQL 5.5 or older
ALTER TABlE toonces.pages
    MODIFY modified_dt datetime ON UPDATE CURRENT_TIMESTAMP;
*/

DROP TABLE IF EXISTS toonces.page_hierarchy_bridge;

CREATE TABLE toonces.page_hierarchy_bridge (
     bridge_id BIGINT NOT NULL auto_increment
    ,page_id BIGINT NOT NULL
    ,ancestor_page_id BIGINT NOT NULL
    ,descendant_page_id BIGINT
    ,created TIMESTAMP NOT NULL
        ,PRIMARY KEY (bridge_id)
        ,FOREIGN KEY (page_id)
            REFERENCES toonces.pages(page_id)/*
        ,FOREIGN KEY (ancestor_page_id)
            REFERENCES toonces.pages(page_id)
        ,FOREIGN KEY (descendant_page_id)
            REFERENCES toonces.pages(page_id)*/
);

DROP TABLE IF EXISTS toonces.blogs;

CREATE TABLE toonces.blogs (
     blog_id BIGINT NOT NULL auto_increment
    ,page_id VARCHAR(50) NOT NULL
    ,created TIMESTAMP NOT NULL
    ,deleted TIMESTAMP NULL
        ,PRIMARY KEY (blog_id)
        -- FOREIGN KEY (page_id)
        -- REFERENCES toonces.pages(page_id)
);

/**************** User, Access & Security Stuff ********************/

DROP TABLE IF EXISTS toonces.users;

CREATE TABLE toonces.users (
     user_id    BIGINT      NOT NULL    AUTO_INCREMENT
    ,email      VARCHAR(40) NOT NULL
    ,nickname   VARCHAR(32) NOT NULL
    ,firstname  VARCHAR(32) NOT NULL
    ,lastname   VARCHAR(32) NOT NULL
    ,password   CHAR(128)   NOT NULL
    ,salt       CHAR(128)   NOT NULL
    ,created    TIMESTAMP   NOT NULL
    ,revoked    TIMESTAMP   NULL
    ,is_admin   BOOL        NOT NULL  DEFAULT 0
        ,PRIMARY KEY (user_id)
        ,UNIQUE INDEX idx_email (email)
);

DROP TABLE IF EXISTS toonces.sessions;

CREATE TABLE toonces.sessions (
     session_id     BIGINT      NOT NULL    AUTO_INCREMENT
    ,user_id        BIGINT      NOT NULL
    ,ip_address     BIGINT      NOT NULL    -- WHAT'S THE BEST FOR STORING THIS?
    ,started        TIMESTAMP   NOT NULL
    ,user_agent     VARCHAR(192)
        ,PRIMARY KEY (session_id)
);

DROP TABLE IF EXISTS toonces.page_user_access;

CREATE TABLE toonces.page_user_access (
     page_user_access_id     BIGINT  NOT NULL    AUTO_INCREMENT
    ,page_id                 BIGINT  NOT NULL
    ,user_id                 BIGINT  NOT NULL
    ,can_edit                BOOL    NOT NULL    DEFAULT 0
        ,PRIMARY KEY (page_user_access_id)
        ,CONSTRAINT idx_pageid_userid UNIQUE INDEX 
        (
             page_id
            ,user_id
        )
DROP TABLE IF EXISTS toonces.login_attempts;

CREATE TABLE toonces.login_attempts (

     login_attempt_id       BIGINT          NOT NULL    AUTO_INCREMENT
    ,attempt_user_id        BIGINT          NULL
    ,attempt_time           TIMESTAMP       NOT NULL
    ,http_client_ip         INT UNSIGNED
    ,http_x_forwarded_for   INT UNSIGNED
    ,remote_addr            INT UNSIGNED
    ,user_agent             VARCHAR(255)

        ,PRIMARY KEY (login_attempt_id)
);

/**************** Site Administration Tools ********************/

DROP TABLE IF EXISTS toonces.adminpages;

CREATE TABLE toonces.adminpages (
     adminpage_id           BIGINT          NOT NULL
    ,admin_parent_page_id   BIGINT          NOT NULL
    ,pathname               VARCHAR(50)
    ,page_title             VARCHAR(100)
    ,page_link_text         VARCHAR(100)
    ,pagebuilder_class      VARCHAR(50)     NOT NULL
    ,pageview_class         VARCHAR(50)     NOT NULL
    ,css_stylesheet         VARCHAR(100)    NOT NULL
    ,created_by             VARCHAR(50)
    ,created_dt             TIMESTAMP       NOT NULL
    ,modified_dt            DATETIME
    ,redirect_on_error      BOOL
    ,published              BOOL

        ,PRIMARY KEY (adminpage_id)
);


