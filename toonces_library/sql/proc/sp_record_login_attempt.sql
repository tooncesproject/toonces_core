-- sp_record_login_attempt
-- Initial Commit: Paul Anderson 4/19/2018
-- Wraps an insert to login_attempt in a transaction so it can return a PK.
DROP PROCEDURE IF EXISTS sp_record_login_attempt;

--%c
DELIMITER //
--/%c

CREATE PROCEDURE sp_record_login_attempt(
     param_attempt_page_id          BIGINT
    ,param_http_method              VARCHAR(10)
    ,param_attempt_user_id          BIGINT UNSIGNED
    ,param_attempt_time             TIMESTAMP
    ,param_http_client_ip           INT UNSIGNED
    ,param_http_x_forwarded_for     INT UNSIGNED
    ,param_remote_addr              INT UNSIGNED
    ,param_user_agent               VARCHAR(1000)
)

BEGIN   
        DECLARE var_login_attempt_id BIGINT;
        DECLARE var_meta_http_method_id BIGINT;

        -- Lookup the http method
        SELECT
            meta_http_method_id
        INTO
            var_meta_http_method_id
        FROM
            meta_http_method
        WHERE
            method_name = param_http_method
        ;


        START TRANSACTION;
            -- Insert the attempt
            INSERT INTO login_attempt
            (
                 page_id
                ,meta_http_method_id
                ,attempt_user_id
                ,attempt_time
                ,http_client_ip
                ,http_x_forwarded_for
                ,remote_addr
                ,user_agent
            ) VALUES (
                 param_attempt_page_id
                ,var_meta_http_method_id
                ,param_attempt_user_id
                ,param_attempt_time
                ,param_http_client_ip
                ,param_http_x_forwarded_for
                ,param_remote_addr
                ,param_user_agent
            );

            -- Get the inserted ID
            SET var_login_attempt_id = LAST_INSERT_ID();
        COMMIT;
    
        -- SELECT the id for output
        SELECT var_login_attempt_id;

END
--%c
//
DELIMITER ;
--/%c
