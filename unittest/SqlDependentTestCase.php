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
include_once '../setupTooncesDatabase.php';

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
                
                self::$pdo = new PDO("mysql:host=$host;port=$port;",$dbUserName,$pw);

            }
            $this->conn = self::$pdo;
        }
        
        return $this->conn;
    }

    // lifted from https://github.com/tymondesigns/jwt-auth/issues/28
    public function makeServerFixture($method, $uri, array $data = [], array $headers = [])
    {
        //if ($this->token && !isset($headers['Content-type'])) {
        //    $headers['Content-type'] = "Bearer: $this->token";
        //}
        
        $server = $this->transformHeadersToServerVars($headers);
        
        $this->call(strtoupper($method), $uri, $data, [], [], $server);
        
        return $this;
    }

    public function destroyTestDatabase() {
        // Drops toonces user and database.
        $sqlConn = $this->getConnection();
        $sql = <<<SQL
        DROP DATABASE toonces;
        DROP USER toonces;
SQL;
        $sqlConn->exec($sql);
            
    }
    
    public function buildTestDatabase() {
        // Calls the setupTooncesDatabase function
        $sqlConn = $this->getConnection();
        $setupFailure = setupTooncesDatabase(
             $sqlConn               // SQL connection object (PDO)
            ,'kittycat'             // Toonces MySQL user password
            ,'email@example.com'    // Toonces admin username
            ,'mySecurePassword'     // Toonces admin password
            ,'Paul'                 // toonces user first name
            ,'Anderson'             // toonces user last name
            ,'God of Toonces'       // toonces user nickname
            ,'%'                    // PHP host IP/domain
            );
        return $setupFailure;
    }
    
    
}

// PROCEDURAL DEPENDENCIES
function apache_request_headers() {
    return array (
        'Accept-version' => 1
    );
}
