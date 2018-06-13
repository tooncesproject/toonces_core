CREATE DATABASE IF NOT EXISTS toonces;

USE toonces;


CREATE TABLE IF NOT EXISTS resource (
     resource_id       BIGINT      NOT NULL AUTO_INCREMENT
    ,pathname          VARCHAR(50) NULL
    ,resource_class    VARCHAR(50) NOT NULL
    ,created_dt        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt       TIMESTAMP   NULL ON UPDATE CURRENT_TIMESTAMP
    ,redirect_on_error BOOL        NOT NULL
    ,published         BOOL        NOT NULL DEFAULT 0

        ,CONSTRAINT pk_resource PRIMARY KEY (resource_id)
        ,INDEX idx_pathname (pathname)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS resource_hierarchy_bridge (
     bridge_id              BIGINT    NOT NULL AUTO_INCREMENT
    ,resource_id            BIGINT    NOT NULL
    ,ancestor_resource_id   BIGINT    NULL
    ,descendant_resource_id BIGINT    NULL
    ,created_dt             TIMESTAMP NOT NULL

        ,CONSTRAINT pk_resource_hierarchy_bridge PRIMARY KEY (bridge_id)
        ,CONSTRAINT fk_rhb_resource FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
        ,CONSTRAINT fk_rhb_ancestor FOREIGN KEY (ancestor_resource_id) REFERENCES resource (resource_id)
        ,CONSTRAINT fk_rhb_descendant FOREIGN KEY (descendant_resource_id) REFERENCES resource (resource_id)
        ,INDEX idx_resource_ancestor (resource_id, ancestor_resource_id)

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


CREATE TABLE IF NOT EXISTS resource_user_access (
     resource_user_access_id     BIGINT         NOT NULL    AUTO_INCREMENT
    ,resource_id             BIGINT         NOT NULL
    ,user_id                 BIGINT         NOT NULL
    ,can_edit                BOOL           NOT NULL    DEFAULT 0
    ,created_dt              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt             TIMESTAMP      NULL ON UPDATE CURRENT_TIMESTAMP

        ,CONSTRAINT pk_resource_user_access PRIMARY KEY (resource_user_access_id)
        ,CONSTRAINT ak_resourceid_userid UNIQUE INDEX (resource_id,user_id)
        ,CONSTRAINT fk_resource_id FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
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
     login_attempt_id     BIGINT        NOT NULL    AUTO_INCREMENT
    ,
     resource_id          BIGINT        NULL
    ,meta_http_method_id  BIGINT        NULL
    ,attempt_user_id      BIGINT        NULL
    ,attempt_time         TIMESTAMP     NOT NULL
    ,http_client_ip       INT UNSIGNED  NULL
    ,http_x_forwarded_for INT UNSIGNED  NULL
    ,remote_addr          INT UNSIGNED  NULL
    ,attempt_success      BOOL          NOT NULL DEFAULT FALSE
    ,user_agent           VARCHAR(1000) NULL

        ,CONSTRAINT pk_login_attempts PRIMARY KEY (login_attempt_id)
        ,CONSTRAINT fk_login_attempt_resource FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
        ,CONSTRAINT fk_login_attempt_user FOREIGN KEY (attempt_user_id) REFERENCES users (user_id)
        ,CONSTRAINT fk_login_attempt_http_method FOREIGN KEY (meta_http_method_id) REFERENCES meta_http_method (meta_http_method_id)
        ,INDEX idx_attempt_time (attempt_time)
        ,INDEX idx_http_client_ip (http_client_ip)
        ,INDEX idx_http_x_forwarded_for (http_x_forwarded_for)
        ,INDEX idx_remote_addr (remote_addr)
        ,INDEX idx_attempt_success (attempt_success)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;


CREATE TABLE IF NOT EXISTS dom_resource (
     dom_resource_id        BIGINT       NOT NULL AUTO_INCREMENT
    ,resource_id            BIGINT       NOT NULL
    ,title                  VARCHAR(200) NOT NULL
    ,template_html_path     VARCHAR(200) NOT NULL
    ,template_client_class  VARCHAR(50) NOT NULL
    ,content_html_path      VARCHAR(200) NOT NULL
    ,content_client_class   VARCHAR(50)  NOT NULL
    ,created_by             VARCHAR(50)  NULL
    ,created_dt             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,modified_dt            TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP

        ,CONSTRAINT pk_dom_resource PRIMARY KEY (dom_resource_id)
        ,CONSTRAINT fk_dom_resource_resource FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
        ,CONSTRAINT ak_resource_id UNIQUE INDEX (resource_id)

) ENGINE=INNODB ROW_FORMAT=COMPRESSED;

