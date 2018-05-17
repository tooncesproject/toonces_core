<?php
require_once 'config.php';
require_once LIBPATH . 'php/utility/UserManager.php';

// function setupTooncesDatabase -
// Creates the toonces MySQL user, grants privileges, builds tables, inserts data, compiles functions and procedures,
// creates the default home page and admin apges.
function setupTooncesDatabase(
    $conn       // SQL connection object (PDO)
    ,$tup        // Toonces MySQL user password
    ,$email      // Toonces admin username
    ,$pw         // Toonces admin password
    ,$firstName  // toonces user first name
    ,$lastName   // toonces user last name
    ,$nickname   // toonces user nickname
    ,$phpHost    // PHP host IP/domain
    )
{

    // Create the Toonces MySQL user
    echo 'Creating \'toonces\' MySQL user...' . PHP_EOL;
    $sql = <<<SQL
        FLUSH PRIVILEGES;
        CREATE USER 'toonces'@:phpHost;
        SET PASSWORD FOR 'toonces'@:phpHost = PASSWORD(:password);

SQL;
    $stmt = $conn->prepare($sql);
    $stmt->fetchAll();
    $stmt->closeCursor();

    try {
        $stmt->execute(['phpHost' => $phpHost, 'password' => $tup]);
    } catch (PDOException $e) {
        echo('Failed to create Toonces database user: ' . $e->getMessage() . PHP_EOL);
        throw $e;
    }

    $stmt->closeCursor();

    // Run the DDL script
    echo 'Building database...' . PHP_EOL;
    $sql = file_get_contents('toonces_library/sql/table/toonces_ddl.sql');
    try {
        $conn->exec($sql);
    } catch (PDOException $e) {
        echo('Failed to build Toonces core database: ' . $e->getMessage() . PHP_EOL);
        throw $e;
    }

    // Grant the Toonces user privileges needed to use the database.
    $sql = <<<SQL
        GRANT SELECT, CREATE, INSERT, DELETE, EXECUTE, UPDATE, ALTER ROUTINE ON toonces.* TO 'toonces'@:phpHost;
SQL;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute(['phpHost' => $phpHost]);
    } catch (PDOException $e) {
        echo('Failed to grant database privileges to Toonces MySQL user: ' . $e->getMessage() . PHP_EOL);
        throw $e;
    }

    // run the data scripts
    echo 'Inserting base data...' . PHP_EOL;
    $dataScripts = scandir('toonces_library/sql/data');
    for ($i = 2; $i < count($dataScripts); ++$i) {

        // Only execute if it's a SQL file
        if (strtolower(substr($dataScripts[$i], -4)) == '.sql') {
            $path = 'toonces_library/sql/data/' . $dataScripts[$i];
            $sql = file_get_contents($path);
            $stmt = $conn->prepare($sql);
            try {
                //$conn->exec($sql);
                $stmt->execute();
                $stmt->closeCursor();
            } catch (PDOException $e) {
                echo('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
                throw $e;
            }
        }
    }

    // run the create function scripts
    echo 'Compiling SQL Functions...' . PHP_EOL;
    $functionScripts = scandir('toonces_library/sql/func');
    for ($i = 2; $i < count($functionScripts); ++$i) {
        if (strtolower(substr($functionScripts[$i], -4)) == '.sql') {
            $path = 'toonces_library/sql/func/' . $functionScripts[$i];
            $sql = file_get_contents($path);
            $sql = str_replace('--%c', '/*', $sql);
            $sql = str_replace('--/%c', '*/', $sql);
            try {
                $isError = $conn->exec($sql);
            } catch (PDOException $e) {
                echo('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
                throw $e;
            }
            // Not all errors raise an exeption, even when PDO is set to do so. PDO sucks.
            if ($isError == 1) {
                throw new Exception('SQL execution failed for file ' . $path . PHP_EOL);
            }
        }
    }

    // Run the create procedure scripts
    echo 'Compiling SQL stored prodedures...' . PHP_EOL;
    $procedureScripts = scandir('toonces_library/sql/proc');
    for ($i = 2; $i < count($procedureScripts); ++$i) {
        if (strtolower(substr($procedureScripts[$i], -4)) == '.sql') {
            $path = 'toonces_library/sql/proc/' . $procedureScripts[$i];
            $sql = file_get_contents($path);
            $sql = str_replace('--%c', '/*', $sql);
            $sql = str_replace('--/%c', '*/', $sql);
            try {
                $isError = $conn->exec($sql);
            } catch (PDOException $e) {
                echo('SQL execution failed for file ' . $path . ': ' . $e->getMessage() . PHP_EOL);
                throw $e;
            }
            // Not all errors raise an exeption, even when PDO is set to do so. PDO sucks.
            if ($isError == 1) {
                throw new Exception('SQL execution failed for file ' . $path . PHP_EOL);
            }
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
        echo('SQL Error: ' . $e->getMessage() . PHP_EOL);
        throw $e;
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
            echo('Failed to create main page: ' . $e->getMessage());
            throw $e;
        }

        // Get the page ID
        $sql = 'SELECT LAST_INSERT_ID()';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $pageID = $rows[0][0];

        // Insert a record into the ext_html_page table
        $sql = "INSERT INTO ext_html_page (page_id, html_path, client_class) VALUES (:pageID, 'toonces_library/html/toonces_welcome.html', 'LocalResourceClient')";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([':pageID' => $pageID]);
        } catch (PDOException $e) {
            echo('Failed to insert a record into ext_html_page: ' . $e->getMessage());
            throw $e;
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
        echo('Failed to create admin pages: ' . $e->getMessage());
        throw $e;
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
        echo('Failed to create Core Services API: ' . $e->getMessage());
        throw $e;
    }
    if ($result) {
        $sql = "CALL sp_delete_page(:pageId)";
        $stmt = $conn->prepare($sql);
        try {
            $stmt->execute(array('pageId' => $result[0][0]));
        } catch (PDOException $e) {
            echo('Failed to create Core Services API: ' . $e->getMessage());
            throw $e;
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
            ,'JsonPageView'                  -- ,pageview_class VARCHAR(50)
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
        echo('Failed to create Core Services API: ' . $e->getMessage());
        throw $e;
    }

    // Pages endpoint
    $sql = <<<SQL
        SELECT CREATE_PAGE (
             :csPageId                          -- parent_page_id BIGINT
            ,'pages'                            -- ,pathname VARCHAR(50)
            ,'Toonces Core Services - Pages'    -- ,page_title VARCHAR(50)
            ,'Toonces Core Services - Pages'    -- ,page_link_text VARCHAR(50)
            ,'PageApiPageBuilder'              -- ,pagebuilder_class VARCHAR(50)
            ,'JsonPageView'                      -- ,pageview_class VARCHAR(50)
            ,FALSE                              -- ,redirect_on_error BOOL
            ,FALSE                              -- ,published BOOL
            ,6                                  -- ,pagetype_id BIGINT
        )
SQL;
    $stmt = $conn->prepare($sql);
    $pagesEndpointPageId = null;
    try {
        $stmt->execute(array('csPageId' => $csPageId));
        $result = $stmt->fetchAll();
        $pagesEndpointPageId = $result[0][0];
    } catch (PDOException $e) {
        echo('Failed to create Core Services API (pages): ' . $e->getMessage());
        throw $e;
    }

    // external content pages endpoint
    $sql = <<<SQL
        SELECT CREATE_PAGE (
             :pagesPageId                               -- parent_page_id BIGINT
            ,'contentpages'                             -- ,pathname VARCHAR(50)
            ,'Toonces Core Services - Content Pages'    -- ,page_title VARCHAR(50)
            ,'Toonces Core Services - Content Pages'    -- ,page_link_text VARCHAR(50)
            ,'ExtPageApiPageBuilder'                    -- ,pagebuilder_class VARCHAR(50)
            ,'JsonPageView'                             -- ,pageview_class VARCHAR(50)
            ,FALSE                                      -- ,redirect_on_error BOOL
            ,FALSE                                      -- ,published BOOL
            ,6                                          -- ,pagetype_id BIGINT
        )
SQL;
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute(array('pagesPageId' => $pagesEndpointPageId));
        $result = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo('Failed to create Core Services API (Content pages): ' . $e->getMessage());
        throw $e;
    }


    // Blogs endpoint
    $sql = <<<SQL
        SELECT CREATE_PAGE (
             :csPageId                          -- parent_page_id BIGINT
            ,'blogs'                            -- ,pathname VARCHAR(50)
            ,'Toonces Core Services - Blogs'    -- ,page_title VARCHAR(50)
            ,'Toonces Core Services - Blogs'    -- ,page_link_text VARCHAR(50)
            ,'BlogsAPIPageBuilder'              -- ,pagebuilder_class VARCHAR(50)
            ,'JsonPageView'                      -- ,pageview_class VARCHAR(50)
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
        echo('Failed to create Core Services API (blogs): ' . $e->getMessage());
        throw $e;
    }

    // Blog Posts endpoint
    $sql = <<<SQL
        SELECT CREATE_PAGE (
             :csPageId                              -- parent_page_id BIGINT
            ,'blogposts'                            -- ,pathname VARCHAR(50)
            ,'Toonces Core Services - Blog Posts'   -- ,page_title VARCHAR(50)
            ,'Toonces Core Services - Blog Posts'   -- ,page_link_text VARCHAR(50)
            ,'BlogPostAPIPageBuilder'               -- ,pagebuilder_class VARCHAR(50)
            ,'JsonPageView'                          -- ,pageview_class VARCHAR(50)
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
        echo('Failed to create Core Services API (blog posts): ' . $e->getMessage());
        throw $e;
    }

    // HTML Files endpoint
    $sql = <<<SQL
        SELECT CREATE_PAGE (
             :csPageId                              -- parent_page_id BIGINT
            ,'htmlresources'                        -- ,pathname VARCHAR(50)
            ,'Toonces Core Services - HTML Files'   -- ,page_title VARCHAR(50)
            ,'Toonces Core Services - HTML Files'   -- ,page_link_text VARCHAR(50)
            ,'DocumentEndpointPageBuilder'          -- ,pagebuilder_class VARCHAR(50)
            ,'FilePageView'                         -- ,pageview_class VARCHAR(50)
            ,TRUE                                   -- ,redirect_on_error BOOL
            ,FALSE                                  -- ,published BOOL
            ,6                                      -- ,pagetype_id BIGINT
        )
SQL;

    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute(array('csPageId' => $csPageId));
        $result = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo('Failed to create Core Services API (HTML resources): ' . $e->getMessage());
        throw $e;
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
            echo('Error: Failed to write MySQL password to toonces_config.xml: ' . $e->getMessage() . PHP_EOL);
            throw $e;
        }

    } else {
        $passwordElement = $nodes->item(0);
        $passwordElement->nodeValue = $tup;
    }
    try {
        $xml->save('toonces-config.xml');
    } catch (Exception $e) {
        echo('Error: Failed to write MySQL password to toonces_config.xml: ' . $e->getMessage() . PHP_EOL);
        throw $e;
    }

    // Create the admin account
    echo 'Creating admin account...' . PHP_EOL;
    $userManager = new UserManager($conn);
    $response = $userManager->createUser($email, $pw, $pw, $firstName, $lastName, $nickname, true);

    // Check UserManager's response for admin user validation.
    while ($fieldName = current($response)) {

        if ($fieldName['responseState'] == 0) {
            echo('Error creating Admin user: ' . $fieldName['responseMessage'] . PHP_EOL);
        }
        next($response);
    }

    echo 'Finished!' . PHP_EOL;

    // Indicate success
    return false;
}
