<?php
/*
 *  
 * @author paulanderson
 * SqlDependentTestCase
 * Initial commit: Paul Anderson, 4/20/2018
 * 
 * Provides a static PDO object factory for unit testing.
 * Also includes methods to build and tear down test fixtures in the test database.
 * (which is why you should NEVER run this in production...)
 *
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
require_once __DIR__ . '/../setupTooncesDatabase.php';

abstract class SqlDependentTestCase extends TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;
    private $conn;
    
    final public function getConnection() {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                $host = $GLOBALS['DB_HOST'];
                $port = $GLOBALS['DB_PORT'];
                $dbUserName = $GLOBALS['DB_USERNAME'];
                $pw = $GLOBALS['DB_PASSWORD'];
                $newPdo = new PDO("mysql:host=$host;port=$port;",$dbUserName,$pw);
                $newPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo = $newPdo;
            }
            $this->conn = self::$pdo;
        }
        
        return $this->conn;
    }


    public function destroyTestDatabase() {
        // Drops toonces user and database.
        $sqlConn = $this->getConnection();
        $sql = <<<SQL
        DROP DATABASE IF EXISTS toonces;
        DROP USER IF EXISTS toonces;
SQL;
        $sqlConn->exec($sql);
            
    }


    public function buildTestDatabase() {
        // Calls the setupTooncesDatabase function
        $sqlConn = $this->getConnection();
        $setupFailure = setupTooncesDatabase(
             $sqlConn                       // SQL connection object (PDO)
            ,'kittycat'                     // Toonces MySQL user password
            ,$GLOBALS['TOONCES_USERNAME']   // Toonces admin username
            ,$GLOBALS['TOONCES_PASSWORD']   // Toonces admin password
            ,'Paul'                         // toonces user first name
            ,'Anderson'                     // toonces user last name
            ,'Dark Lord of Toonces'         // toonces user nickname
            ,'%'                            // PHP host IP/domain
            );
        return $setupFailure;
    }


    public function createNonAdminUser() {
        // Create a non-admin Toonces user
        $sqlConn = $this->getConnection();
        $userManager = new UserManager($sqlConn);
        $response = $userManager->createUser(
             $GLOBALS['NON_ADMIN_USERNAME']
            ,$GLOBALS['NON_ADMIN_PASSWORD']
            ,$GLOBALS['NON_ADMIN_PASSWORD']
            ,'Jane'
            ,'User'
            ,'Non Admin User'
            , false
            );
        return $response;
    }


    public function createUnpublishedPage() {
        // Create an unpublished page; non-admin users don't have access.
        $sqlConn = $this->getConnection();
        $sql = <<<SQL
        SELECT CREATE_PAGE  (
             1                              -- parent_page_id BIGINT
            ,'unpublished_page'             -- pathname VARCHAR(50)
            ,'Unpublished Page'             -- page_title VARCHAR(50)
            ,'Unpublished Page'             -- page_link_text VARCHAR(50)
            ,'Toonces404PageBuilder'        -- pagebuilder_class VARCHAR(50)
            ,'HTMLPageView'                 -- pageview_class VARCHAR(50)
            ,FALSE                          -- redirect_on_error BOOL
            ,FALSE                          -- published BOOL
            ,0                              -- pagetype_id BIGINT
)
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
    
        $newPageId = $result[0][0];
        
        return $newPageId;
        
    }


    public function unsetBasicAuth() {
        // Unsets the user/password basic auth server variables

        if (isset($_SERVER['PHP_AUTH_USER']))
            unset($_SERVER['PHP_AUTH_USER']);
        
        if(isset($_SERVER['PHP_AUTH_PW']))
            unset($_SERVER['PHP_AUTH_PW']);
    }


    public function setBadAuth() {
        // Sets bogus auth server variabes
        $_SERVER['PHP_AUTH_USER'] = 'badguy@evil.com';
        $_SERVER['PHP_AUTH_PW'] = 'badguyPassword';
    }


    public function setAdminAuth() {
        // Injects admin user basic auth into server globals.
        $_SERVER['PHP_AUTH_USER'] = $GLOBALS['TOONCES_USERNAME'];
        $_SERVER['PHP_AUTH_PW'] = $GLOBALS['TOONCES_PASSWORD'];
    }


    public function setNonAdminAuth() {
        // Injects non-admin user basic auth info into server globals.
        $_SERVER['PHP_AUTH_USER'] = $GLOBALS['NON_ADMIN_USERNAME'];
        $_SERVER['PHP_AUTH_PW'] = $GLOBALS['NON_ADMIN_PASSWORD'];
    }
}

