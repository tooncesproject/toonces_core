# toonces_core
A cute little LAMP app framework.
Because fuck Wordpress. A work in progress.

# How To Install:
To use Toonces: We assume you already have a working familiarity with MySQL, Apache, PHP and other aspects of a LAMP stack, and that these components are already installed on your server or test environment.

1. Clone the repository and copy the files within the repository folder to your PHP document root folder.

2. Configure your Apache instance:

	In httpd.conf, uncomment the following lines:
	
	*LoadModule rewrite\_module libexec/apache2/mod\_rewrite.so
	LoadModule php5\_module libexec/apache2/libphp5.so*
	
	In the **DocumentRoot Folder** section of httpd.conf, change **AllowOverride None** to **AllowOverride All**

3. Configure PHP:
	
	In your **php.ini** file:
	
	Set up the default timezone, i.e.:
	> 	[Date]
	> 	; Defines the default timezone used by the date 	functions
	> 	; http://php.net/date.timezone
	> 	date.timezone = America/Los_Angeles  
	
	Add the following line:
	>	output_buffering = On
	
4. Run the **install_toonces.php** script. *Usage: php install_toonces.php mh=[MySQL Host] mu=[MySQL Username] mp=[MySQL Password] tup=[Toonces Mysql User Password] email=[Toonces Admin Email address] pw=[Toonces Admin Password] firstname=[First name] lastname=[Last name] nickname=[Nickname]*

 *mh:* MySQL host IP address (Should be 127.0.0.1 in most cases, if you are installing Toonces on the same machine hosting the MySQL cluster).
 
 *mu:* MySQL root username (root recommended)
 
 *mp:* MySQL Root password
 
 *tup:* New password for Toonces MySQL user
 
 *email:* Email address as user name for root Toonces admin account
 
 *pw:* Password for root Toonces admin account
 
 *firstname:* First name of Toonces admin root user
 
 *lastname:* Last name of Toonces admin root user
 
 *nickname:* Nickname of Toonces admin root user
