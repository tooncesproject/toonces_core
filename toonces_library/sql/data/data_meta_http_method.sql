INSERT INTO meta_http_method
(
     meta_http_method_id
    ,method_name
) VALUES
     (1,        'GET')
    ,(2,        'PUT')
    ,(3,        'POST')
    ,(4,        'HEAD')
    ,(5,        'DELETE')
    ,(6,        'OPTIONS')
    ,(7,        'CONNECT')
ON DUPLICATE KEY UPDATE
     method_name = VALUES (method_name)

;