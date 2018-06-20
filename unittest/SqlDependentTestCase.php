<?php
/**
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

    protected function setUp() {
        // Set server variables so xdebug doesn't break the unit tests
        $_SERVER['REQUEST_METHOD'] = null;
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_USER_AGENT'] = '';
        $_SERVER['REMOTE_ADDR'] = null;
    }

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


        // Look up the user ID
        $sql = <<<SQL
        SELECT user_id
        FROM users
        WHERE email = :nonAdminUsername
SQL;
        $stmt = $sqlConn->prepare($sql);
        $stmt->execute(['nonAdminUsername' => $GLOBALS['NON_ADMIN_USERNAME']]);
        $result = $stmt->fetchAll();
        $userId = intval($result[0][0]);


        return $userId;
    }


    public function createPage($published = true, $parentResourceId = 1, $pathName = null) {
        // Create an unpublished resource; non-admin users don't have access.
        $sqlConn = $this->getConnection();
        // In some cases, in case this is called twice in the same fixture,
        // we need to make the pathame unique. We'll use the expected resource_id
        if (!$pathName) {
            $sql = "SELECT MAX(resource_id) + 1 FROM resource";
            $stmt = $sqlConn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchall();
            $identifier = strval($result[0][0]);
            $pathName = 'unpublished_page_' . $identifier;
        }

        $sql = <<<SQL
        SELECT CREATE_RESOURCE  (
             :parentResourceId                  -- parent_resource_id BIGINT
            ,:pathName                      -- pathname VARCHAR(50)
            ,'TooncesWelcomeDomDocumentResource'        -- resource_class VARCHAR(50)
            ,FALSE                          -- redirect_on_error BOOL
            ,:published                     -- published BOOL
)
SQL;
        $stmt = $sqlConn->prepare($sql);

        $stmt->execute(['parentResourceId' => $parentResourceId, 'pathName' => $pathName, 'published' => intval($published)]);
        $result = $stmt->fetchAll();

        $newResourceId = intval($result[0][0]);

        return $newResourceId;

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
