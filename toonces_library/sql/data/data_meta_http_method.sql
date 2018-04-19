INSERT INTO meta_http_method
(
     meta_http_method_id
    ,method_name
) VALUES

ON DUPLICATE KEY UPDATE
     method_name = VALUES (method_name)

;