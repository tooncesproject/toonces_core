<?php
/*
 * install_toonces.php
 * Initial commit: Paul Anderson, 2017.10.18
 * 
 * Install script to build the Toonces MySQL database
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
echo 'Connecting to MySQL database...' . PHP_EOL;
try {
    $conn = new PDO("mysql:host=$mh",$mu,$mp);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('MySQL Connection failed: '. $e->getMessage() . PHP_EOL);
}

// Create the Toonces MySQL user
echo 'Creating \'toonces\' MySQL user...' . PHP_EOL;
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
echo 'Building database...' . PHP_EOL;
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
echo 'Inserting base data...' . PHP_EOL;
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
echo 'Compiling SQL Functions...' . PHP_EOL;
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
echo 'Compiling SQL stored prodedures...' . PHP_EOL;
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
        throw new Exception('SQL execution failed for file ' . $path . PHP_EOL);
    }
}

// Run the setup procedures
echo 'Creating default home page...' . PHP_EOL;

// Check for any existing pages:
$sql = 'SELECT page_id FROM toonces.pages';
$stmt = $conn->prepare($sql);

try {
    $stmt->execute();
} catch (Exception $e) {
    die('SQL Error: ' . $e->getMessage() . PHP_EOL);
}

$rows = $stmt->fetchAll();
if (count($rows) == 0) {
    // Create main page if it doesn't already exist.
    $sql = <<<SQL
    INSERT INTO pages (
     page_title
    ,page_link_text
    ,pagebuilder_class
    ,pageview_class
    ,redirect_on_error
    ,published
    ,pagetype_id
    ) VALUES (
     'Sorry, This is Toonces.'
    ,'Home Page'
    ,'ExtHTMLPageBuilder'
    ,'HTMLPageView'
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
} else {
    echo '    Detected existing home page in database; Skipping.' . PHP_EOL;  
}

// Create admin pages
echo 'Creating Toonces admin tools...' . PHP_EOL;
$sql = "CALL sp_create_admin_pages(FALSE)";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} catch (PDOException $e) {
    die('Failed to create admin pages: ' . $e->getMessage());
}

// Create Core Services API
echo 'Creating Core Services API...' . PHP_EOL;

// Does the coreservices page already exist? If so, delete it.
$result = null;
$sql = <<<SQL
    SELECT
        p.page_id
    FROM page_hierarchy_bridge phb
    JOIN pages p ON phb.descendant_page_id = p.page_id
    WHERE
        phb.page_id = 1
        AND
        p.pathname = 'coreservices'
SQL;
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();
} catch (PDOException $e) {
    die ('Failed to create Core Services API: ' . $e->getMessage());
}
if ($result) {
    $sql = "CALL sp_delete_page(:pageId)";
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute(array('pageId' => $result[0][0]));
    } catch (PDOException $e) {
        die ('Failed to create Core Services API: ' . $e->getMessage());
    }
}

// Now create the core services endpoints.
// Core Services root
$csPageId = null;
$sql = <<<SQL
    SELECT CREATE_PAGE (
         1                              -- parent_page_id BIGINT
        ,'coreservices'                 -- ,pathname VARCHAR(50)
        ,'Toonces Core Services'        -- ,page_title VARCHAR(50)
        ,'Toonces Core Services'        -- ,page_link_text VARCHAR(50)
        ,'CoreServicesAPIPageBuilder'   -- ,pagebuilder_class VARCHAR(50)
        ,'APIPageView'                  -- ,pageview_class VARCHAR(50)
        ,FALSE                          -- ,redirect_on_error BOOL
        ,FALSE                          -- ,published BOOL
        ,6                              -- ,pagetype_id BIGINT
    )
SQL;
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $result = $stmt->fetchAll();
    $csPageId = $result[0][0];
} catch (PDOException $e) {
    die ('Failed to create Core Services API: ' . $e->getMessage());
}

// Blogs endpoint
$sql = <<<SQL
    SELECT CREATE_PAGE (
         :csPageId                          -- parent_page_id BIGINT
        ,'blogs'                            -- ,pathname VARCHAR(50)
        ,'Toonces Core Services - Blogs'    -- ,page_title VARCHAR(50)
        ,'Toonces Core Services - Blogs'    -- ,page_link_text VARCHAR(50)
        ,'BlogsAPIPageBuilder'              -- ,pagebuilder_class VARCHAR(50)
        ,'APIPageView'                      -- ,pageview_class VARCHAR(50)
        ,FALSE                              -- ,redirect_on_error BOOL
        ,FALSE                              -- ,published BOOL
        ,6                                  -- ,pagetype_id BIGINT
    )
SQL;
$stmt = $conn->prepare($sql);
try {
    $stmt->execute(array('csPageId' => $csPageId));
    $result = $stmt->fetchAll();
} catch (PDOException $e) {
    die ('Failed to create Core Services API (blogs): ' . $e->getMessage());
}

// Blog Posts endpoint
$sql = <<<SQL
    SELECT CREATE_PAGE (
         :csPageId                              -- parent_page_id BIGINT
        ,'blogposts'                            -- ,pathname VARCHAR(50)
        ,'Toonces Core Services - Blog Posts'   -- ,page_title VARCHAR(50)
        ,'Toonces Core Services - Blog Posts'   -- ,page_link_text VARCHAR(50)
        ,'BlogPostAPIPageBuilder'               -- ,pagebuilder_class VARCHAR(50)
        ,'APIPageView'                          -- ,pageview_class VARCHAR(50)
        ,FALSE                                  -- ,redirect_on_error BOOL
        ,FALSE                                  -- ,published BOOL
        ,6                                      -- ,pagetype_id BIGINT
    )
SQL;
$stmt = $conn->prepare($sql);
try {
    $stmt->execute(array('csPageId' => $csPageId));
    $result = $stmt->fetchAll();
} catch (PDOException $e) {
    die ('Failed to create Core Services API (blog posts): ' . $e->getMessage());
}

// Write the SQL credentials to toonces_config.xml
// code tips from: https://stackoverflow.com/questions/2038535/create-new-xml-file-and-write-data-to-it
echo 'Updating toonces-config.xml...' . PHP_EOL;
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
echo 'Creating admin account...' . PHP_EOL;
$userManager = new UserManager($conn);
$response = $userManager->createUser($email, $pw, $pw, $firstName, $lastName, $nickname, true);

// Check UserManager's response for admin user validation.
while ($fieldName = current($response)) {
    
    if ($fieldName['responseState'] == 0) {
        die('Error creating Admin user: ' . $fieldName['responseMessage'] . PHP_EOL);
    }
    next($response);
}

echo 'Finished!' . PHP_EOL;
