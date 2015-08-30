
DROP DATABASE IF EXISTS toonces;
CREATE DATABASE toonces;

DROP TABLE IF EXISTS toonces.blog_posts;

CREATE TABLE toonces.blog_posts (
	blog_post_id BIGINT NOT NULL AUTO_INCREMENT,
	blog_id BIGINT NOT NULL,
	created_dt DATETIME NOT NULL,
	modified_dt DATETIME NOT NULL,
	created_by VARCHAR(50),
	author VARCHAR(50),
	title TEXT,
	body text,

	PRIMARY KEY (blog_post_id)
	
);


ALTER TABLE toonces.blog_posts
	MODIFY created_dt datetime DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE toonces.blog_posts 
	MODIFY modified_dt datetime ON UPDATE CURRENT_TIMESTAMP;

DROP TABLE IF EXISTS toonces.pages;

CREATE TABLE toonces.pages (
	page_id BIGINT NOT NULL auto_increment,
	pathname VARCHAR(50),
	page_title VARCHAR(50),
	pagebuilder_class VARCHAR(50) NOT NULL,
	pageview_class VARCHAR(50) NOT NULL,
	hierarchy_level INT NOT NULL,
	parent_page_id BIGINT,
	css_stylesheet VARCHAR(100) NOT NULL,
	created_by VARCHAR(50),
	created_dt DATETIME NOT NULL,
	modified_dt DATETIME NOT NULL,

	PRIMARY KEY (page_id)
);

ALTER TABlE toonces.pages
	MODIFY created_dt datetime DEFAULT CURRENT_TIMESTAMP;

ALTER TABlE toonces.pages
	MODIFY modified_dt datetime ON UPDATE CURRENT_TIMESTAMP;

