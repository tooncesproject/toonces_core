
DROP DATABASE IF EXISTS toonces;
CREATE DATABASE toonces;

DROP TABLE IF EXISTS toonces.blog_posts;

CREATE TABLE toonces.blog_posts (
	blog_post_id BIGINT NOT NULL AUTO_INCREMENT,
	blog_id BIGINT NOT NULL,
	page_id BIGINT NOT NULL,
	created_dt DATETIME NOT NULL,
	modified_dt DATETIME NOT NULL,
	created_by VARCHAR(50),
	author VARCHAR(50),
	title VARCHAR(200),
	body TEXT,
	thumbnail_image_vector VARCHAR(50),
	published BOOL,

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
	page_title VARCHAR(100),
	page_link_text VARCHAR(100),
	pagebuilder_class VARCHAR(50) NOT NULL,
	pageview_class VARCHAR(50) NOT NULL,
	css_stylesheet VARCHAR(100) NOT NULL,
	created_by VARCHAR(50),
	created_dt DATETIME NOT NULL,
	modified_dt DATETIME,
	redirect_on_error BOOL,
	page_active BOOL,

	PRIMARY KEY (page_id)
);

ALTER TABlE toonces.pages
	MODIFY created_dt datetime DEFAULT CURRENT_TIMESTAMP;

ALTER TABlE toonces.pages
	MODIFY modified_dt datetime ON UPDATE CURRENT_TIMESTAMP;

DROP TABLE IF EXISTS toonces.page_hierarchy_bridge;

CREATE TABLE toonces.page_hierarchy_bridge (
	bridge_id BIGINT NOT NULL auto_increment,
	page_id BIGINT NOT NULL,
	ancestor_page_id BIGINT NOT NULL,
	descendant_page_id BIGINT,
		PRIMARY KEY (bridge_id),
		FOREIGN KEY (page_id)
			REFERENCES toonces.pages(page_id)/*,
		FOREIGN KEY (ancestor_page_id)
			REFERENCES toonces.pages(page_id),
		FOREIGN KEY (descendant_page_id)
			REFERENCES toonces.pages(page_id)*/
);

DROP TABLE IF EXISTS toonces.blogs;

CREATE TABLE toonces.blogs (
	blog_id BIGINT NOT NULL auto_increment,
	page_id VARCHAR(50) NOT NULL,
		PRIMARY KEY (blog_id)
		-- FOREIGN KEY (page_id)
		-- REFERENCES toonces.pages(page_id)
);

