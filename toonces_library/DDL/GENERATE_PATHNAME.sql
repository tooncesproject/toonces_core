DROP FUNCTION IF EXISTS toonces.GENERATE_PATHNAME; 

DELIMITER // 

CREATE FUNCTION toonces.GENERATE_PATHNAME( str VARCHAR(100) ) RETURNS VARCHAR(50) 
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