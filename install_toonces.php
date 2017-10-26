<?php
/*
 * install_toonces.php
 * Initial commit: Paul Anderson, 2017.10.18
 * 
 * Install script to build 
*/

require_once 'config.php';
require_once 'toonces_library/php/utility/UserManager.php';

// MySQL Parameters
$mh = null;         // MySQL host
$mu = null;         // MySQL username
$mp = null;         // MySQL password
$tup = null;        // Toonces MySQL user password
$email  = null;     // Toonces admin username
$pw = null;         // Toonces admin password
$firstName = null;  // toonces user first name
$lastName = null;   // toonces user last name
$nickName = null;   // toonces user nickname


// Other primitives
$args = array();

// Objects
$conn = null;

// Parse arguments
for ($i = 1; $i < $argc; ++$i ) {
    list($key, $val) = explode('=', $argv[$i]);
    $args[$key] = $val;
}

// Get parameters:
$mh = $args['mh'];
$mu = $args['mu'];
$mp = $args['mp'];
$tup = $args['tup'];
$email = $args['email'];
$pw = $args['pw'];
$firstName = $args['firstname'];
$lastName = $args['lastname'];
$nickname = $args['nickname'];

// Check for required arguments
$allParamsPresent = isset($mh) && isset($mu) && isset($mp) && isset($tup) && isset($email) && isset($pw) && isset($firstName) && isset($lastName) && isset($nickname);

if (!$allParamsPresent) {
    $usageStr = 'Usage: php install_toonces.php mh=[MySQL Host] mu=[MySQL Username] mp=[MySQL Password] tup=[Toonces Mysql User Password] email=[Toonces Admin Email address] pw=[Toonces Admin Password] firstname=[First name] lastname=[Last name] nickname=[Nickname]' . PHP_EOL;
    die($usageStr);
}

// Set up MySQL Connection
try {
    $conn = new PDO("mysql:host=$mh",$mu,$mp);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('MySQL Connection failed: '. $e->getMessage() . PHP_EOL);
}

// Create the Toonces MySQL user
$sql = <<<SQL
    FLUSH PRIVILEGES;
    CREATE USER 'toonces'@'localhost';
    SET PASSWORD FOR 'toonces'@'localhost' = PASSWORD(:password);
        
SQL;
$stmt = $conn->prepare($sql);
$stmt->fetchAll();
$stmt->closeCursor();

try {
    $stmt->execute(['password' => $tup]);
} catch (PDOException $e) {
    die('Failed to create Toonces database user: ' . $e->getMessage() . PHP_EOL);  
}

$stmt->closeCursor();

// Run the DDL script
$sql = file_get_contents('toonces_library/sql/table/toonces_ddl.sql');
try {
    $conn->exec($sql);
} catch (PDOException $e) {
    die('Failed to build Toonces core database: ' . $e->getMessage() . PHP_EOL);
}

// Grant the Toonces user privileges needed to use the database.
$sql = <<<SQL
    GRANT SELECT, CREATE, INSERT, DELETE, EXECUTE, UPDATE, ALTER ROUTINE ON toonces.* TO 'toonces'@'localhost';
SQL;
try {
    $conn->exec($sql);
} catch (PDOException $e) {
    die('Failed to grant database privileges to Toonces MySQL user: ' . $e->getMessage() . PHP_EOL);
}

// run the data scripts
$dataScripts = scandir('toonces_library/sql/data');
for ($i = 2; $i < count($dataScripts); ++$i) { 
    
    $path = 'toonces_library/sql/data/' . $dataScripts[$i];
    $sql = file_get_contents($path);
    $stmt = $conn->prepare($sql);
    try {
        //$conn->exec($sql);
        $stmt->execute();
        $stmt->closeCursor();
    } catch (PDOException $e) {
        die('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
    }
}

// run the create function scripts
$functionScripts = scandir('toonces_library/sql/func');
for ($i = 2; $i < count($functionScripts); ++$i) {
    $path = 'toonces_library/sql/func/' . $functionScripts[$i];
    $sql = file_get_contents($path);
    $sql = str_replace('--%c', '/*', $sql);
    $sql = str_replace('--/%c', '*/', $sql);
    try {
        $isError = $conn->exec($sql);
    } catch (PDOException $e) {
        die('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
    }
    // Not all errors raise an exeption, even when PDO is set to do so. PDO sucks.
    if ($isError == 1) {
        throw new Exception('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
    }
}

// Run the create procedure scripts 
$procedureScripts = scandir('toonces_library/sql/proc');
for ($i = 2; $i < count($procedureScripts); ++$i) {
    $path = 'toonces_library/sql/proc/' . $procedureScripts[$i];
    $sql = file_get_contents($path);
    $sql = str_replace('--%c', '/*', $sql);
    $sql = str_replace('--/%c', '*/', $sql);
    try {
        $isError = $conn->exec($sql);
    } catch (PDOException $e) {
        die('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
    }
    // Not all errors raise an exeption, even when PDO is set to do so. PDO sucks.
    if ($isError == 1) {
        throw new Exception('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
    }
}

// Run the setup procedures
// Create main page
$sql = <<<SQL
INSERT INTO pages (
     page_title
    ,page_link_text
    ,pagebuilder_class
    ,pageview_class
    ,css_stylesheet
    ,redirect_on_error
    ,published
    ,pagetype_id
) VALUES (
     'Sorry, This is Toonces.'
    ,'Home Page'
    ,'ExtHTMLPageBuilder'
    ,'PageView'
    ,'toonces.css'
    ,FALSE
    ,TRUE
    ,5
);

SQL;

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} catch (PDOException $e) {
   die('Failed to create main page: ' . $e->getMessage()); 
}

// Get the page ID
$sql = 'SELECT LAST_INSERT_ID()';
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();
$pageID = $rows[0][0];

// Insert a record into the ext_html_pages table
$sql = "INSERT INTO ext_html_pages (page_id, html_path) VALUES (:pageID, 'toonces_library/html/toonces_welcome.html')";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([':pageID' => $pageID]);
} catch (PDOException $e) {
    die('Failed to insert a record into ext_html_pages: ' . $e->getMessage());
}


// Create admin pages
$sql = "CALL sp_create_admin_pages(FALSE)";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} catch (PDOException $e) {
    die('Failed to create admin pages: ' . $e->getMessage());
}

// Write the SQL credentials to toonces_config.xml
// code tips from: https://stackoverflow.com/questions/2038535/create-new-xml-file-and-write-data-to-it

$xml = new DOMDocument();
$xml->load('toonces-config.xml');
$nodes = $xml->getElementsByTagName('sql_password');
if ($nodes->length == 0) {
    $xmlRoot = $xml->getElementsByTagName('toonces_settings');
    $passwordElement = $xml->createElement('sql_password', $tup);
    try {
        $rootElement = $xmlRoot->item(0);
        $rootElement->appendChild($passwordElement);
         
        } catch (Exception $e) {
            die('Error: Failed to write MySQL password to toonces_config.xml: ' . $e . PHP_EOL);
        }
    
} else {
    $passwordElement = $nodes->item(0);
    $passwordElement->nodeValue = $tup;
}
try {
    $xml->save('toonces-config.xml');
} catch (Exception $e) {
    die('Error: Failed to write MySQL password to toonces_config.xml: ' . $e . PHP_EOL);
}

// Create the admin account
$userManager = new UserManager();
$userManager->conn = $conn;
$ra = $userManager->createUser($email, $pw, $pw, $firstName, $lastName, $nickname, true);
echo var_dump($ra).PHP_EOL;

