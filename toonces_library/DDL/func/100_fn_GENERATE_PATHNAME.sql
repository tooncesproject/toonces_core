-- GENERATE_PATHNAME
-- creates a URL name based on a page name.
-- Makes it all lowercase and free of funky characters.
DROP FUNCTION IF EXISTS GENERATE_PATHNAME; 

DELIMITER // 

CREATE FUNCTION GENERATE_PATHNAME ( str VARCHAR(100) ) RETURNS VARCHAR(50)

DETERMINISTIC

BEGIN
 
    DECLARE i, len SMALLINT DEFAULT 1;
    DECLARE ret VARCHAR(50) DEFAULT '';
    DECLARE c CHAR(1);
    SET len = LEAST(CHAR_LENGTH( str ),50);

    -- Truncate to 50 chars and trim any trailing spaces
    SET str = LEFT(str,50);

    -- remove any non-alphanumeric chars
    REPEAT 
    BEGIN 
        SET c = MID( str, i, 1 );
        IF c = ' ' THEN
            SET ret = CONCAT(ret,' ');
        ELSE 
            IF c REGEXP '[[:alnum:]]' THEN 
                SET ret = CONCAT(ret,c); 
            END IF; 
        END IF;
        SET i = i + 1;
    END; 
    UNTIL i > len END REPEAT;

    -- trim any traling spaces
    SET ret = TRIM(ret);

    -- replace spaces with underscores
    SET ret = REPLACE(ret, ' ','_');

    -- Remove any extra spaces
    WHILE INSTR(ret, '  ') > 0 DO
        SET ret := replace(ret, '  ', ' ');
    END WHILE;

    -- lowercase it
    SET ret = lcase(ret);
  RETURN ret; 
END // 
DELIMITER ; 
