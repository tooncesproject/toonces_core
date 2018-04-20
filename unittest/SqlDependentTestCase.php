<?php
/*
 *  
 * @author paulanderson
 * SqlDependentTestCase
 * Initial commit: Paul Anderson, 4/20/2018
 * 
 * Provides a static PDO object factory for unit testing.
 *
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class SqlDependentTestCase extends TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;
    private $conn;
    
    final public function getConnection()
    {
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
}
