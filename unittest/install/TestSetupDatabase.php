<?php
/*
 * TestSetupDatabase.php
 * Initial commit: Paul Anderson, 4/20/2018
 * 
 * PHPUnit test case for the Toonces database installation script.
 * NOTE: Be careful with the database connection globals as set up in phpunit.xml - 
 * This test will delete the toonces database if it exists.
 * Don't try this at home.
 * Don't operate this in a production environment.
*/

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../setupTooncesDatabase.php';
require_once __DIR__ . '../../SqlDependentTestCase.php';

class TestSetupTooncesDatabase extends SqlDependentTestCase {
    
    public function testSetupDatabase() {
        
        // ARRANGE
        $sqlConn = $this->getConnection();
        $this->destroyTestDatabase();
        // ACT
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

        // Tear down db
        $this->destroyTestDatabase();

        // ASSERT
        $this->assertFalse($setupFailure);
    
    }
}
