CREATE DATABASE IF NOT EXISTS toonces;

USE toonces;



CREATE TABLE IF NOT EXISTS pages (
     page_id            BIGINT          NOT NULL AUTO_INCREMENT
    ,pathname           VARCHAR(50)     NULL
    ,page_title         VARCHAR(100)    NULL
    ,page_link_text     VARCHAR(100)    NULL
    ,pagebuilder_class  VARCHAR(50)     NOT NULL
    ,pageview_class     VARCHAR(50)     NOT NULL
    ,created_dt         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt        TIMESTAMP       NULL ON UPDATE CURRENT_TIMESTAMP
    ,deleted            TIMESTAMP       NULL
    ,redirect_on_error  BOOL            NOT NULL
    ,published          BOOL            NOT NULL DEFAULT 0

        ,CONSTRAINT pk_pages PRIMARY KEY (page_id)
        ,INDEX idx_pathname (pathname)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS page_hierarchy_bridge (
     bridge_id          BIGINT      NOT NULL AUTO_INCREMENT
    ,page_id            BIGINT      NOT NULL
    ,ancestor_page_id   BIGINT      NULL
    ,descendant_page_id BIGINT      NULL
    ,created_dt         TIMESTAMP   NOT NULL

        ,CONSTRAINT pk_page_hierarchy_bridge PRIMARY KEY (bridge_id)
        ,CONSTRAINT fk_phb_page FOREIGN KEY (page_id) REFERENCES pages (page_id)
        ,CONSTRAINT fk_phb_ancestor FOREIGN KEY (ancestor_page_id) REFERENCES pages (page_id)
        ,CONSTRAINT fk_phb_descendant FOREIGN KEY (descendant_page_id) REFERENCES pages (page_id)
        ,INDEX idx_page_ancestor (page_id, ancestor_page_id)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS users (
     user_id        BIGINT      NOT NULL    AUTO_INCREMENT
    ,email          VARCHAR(40) NOT NULL
    ,nickname       VARCHAR(32) NOT NULL
    ,firstname      VARCHAR(32) NOT NULL
    ,lastname       VARCHAR(32) NOT NULL
    ,password       CHAR(128)   NOT NULL
    ,salt           CHAR(128)   NOT NULL
    ,created        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt    TIMESTAMP   NULL ON UPDATE CURRENT_TIMESTAMP
    ,revoked        TIMESTAMP   NULL
    ,is_admin       BOOL        NOT NULL  DEFAULT 0

        ,CONSTRAINT pk_users PRIMARY KEY (user_id)
        ,CONSTRAINT ak_email UNIQUE KEY (email)
        ,CONSTRAINT ak_nickname UNIQUE KEY (nickname)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS sessions (
     session_id     BIGINT          NOT NULL    AUTO_INCREMENT
    ,user_id        BIGINT          NOT NULL
    ,ip_address     BIGINT          NOT NULL    -- WHAT'S THE BEST FOR STORING THIS?
    ,started        TIMESTAMP       NOT NULL
    ,user_agent     VARCHAR(1000)   NULL

        ,CONSTRAINT pk_sessions PRIMARY KEY (session_id)
        ,CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users (user_id)
        ,INDEX IDX_session_ip (ip_address)
        ,INDEX idx_session_started (started)


) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS page_user_access (
     page_user_access_id     BIGINT         NOT NULL    AUTO_INCREMENT
    ,page_id                 BIGINT         NOT NULL
    ,user_id                 BIGINT         NOT NULL
    ,can_edit                BOOL           NOT NULL    DEFAULT 0
    ,created_dt              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt             TIMESTAMP      NULL ON UPDATE CURRENT_TIMESTAMP

        ,CONSTRAINT pk_page_user_access PRIMARY KEY (page_user_access_id)
        ,CONSTRAINT ak_pageid_userid UNIQUE INDEX (page_id,user_id)
        ,CONSTRAINT fk_page_id FOREIGN KEY (page_id) REFERENCES pages(page_id)
        ,CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(user_id)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS meta_http_method (
     meta_http_method_id        BIGINT          NOT NULL
    ,method_name                VARCHAR(10)     NOT NULL
    ,created_dt                 TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt                TIMESTAMP       NULL ON UPDATE CURRENT_TIMESTAMP

        ,CONSTRAINT pk_meta_http_method PRIMARY KEY (meta_http_method_id)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS login_attempt (
     login_attempt_id       BIGINT          NOT NULL    AUTO_INCREMENT
    ,page_id                BIGINT          NULL
    ,meta_http_method_id    BIGINT          NULL
    ,attempt_user_id        BIGINT          NULL
    ,attempt_time           TIMESTAMP       NOT NULL
    ,http_client_ip         INT UNSIGNED    NULL
    ,http_x_forwarded_for   INT UNSIGNED    NULL
    ,remote_addr            INT UNSIGNED    NULL
    ,attempt_success        BOOL            NOT NULL DEFAULT FALSE
    ,user_agent             VARCHAR(1000)   NULL

        ,CONSTRAINT pk_login_attempts PRIMARY KEY (login_attempt_id)
        ,CONSTRAINT fk_login_attempt_page FOREIGN KEY (page_id) REFERENCES pages (page_id)
        ,CONSTRAINT fk_login_attempt_user FOREIGN KEY (attempt_user_id) REFERENCES users (user_id)
        ,CONSTRAINT fk_login_attempt_http_method FOREIGN KEY (meta_http_method_id) REFERENCES meta_http_method (meta_http_method_id)
        ,INDEX idx_attempt_time (attempt_time)
        ,INDEX idx_http_client_ip (http_client_ip)
        ,INDEX idx_http_x_forwarded_for (http_x_forwarded_for)
        ,INDEX idx_remote_addr (remote_addr)
        ,INDEX idx_attempt_success (attempt_success)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS ext_html_page (
     ext_html_page_id       BIGINT          NOT NULL AUTO_INCREMENT
    ,page_id                BIGINT          NOT NULL
    ,html_path              VARCHAR(200)    NOT NULL
    ,client_class           VARCHAR(50)     NOT NULL
    ,created_by             VARCHAR(50)     NULL
    ,created_dt             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt            TIMESTAMP       NULL ON UPDATE CURRENT_TIMESTAMP

        ,CONSTRAINT pk_ext_html_page PRIMARY KEY (ext_html_page_id)
        ,CONSTRAINT fk_ext_html_page_pages FOREIGN KEY (page_id) REFERENCES pages (page_id)
        ,CONSTRAINT ak_page_id UNIQUE INDEX (page_id)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;

