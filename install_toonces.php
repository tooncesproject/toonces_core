<?php
/*
 * install_toonces.php
 * Initial commit: Paul Anderson, 2017.10.18
 * 
 * Install script to build the Toonces MySQL database
*/

require_once 'config.php';
require_once 'setupTooncesDatabase.php';


// MySQL Parameters
$mh = null;         // MySQL host
$mu = null;         // MySQL username
$mp = null;         // MySQL password
$tup = null;        // Toonces MySQL user password
$email  = null;     // Toonces admin username
$pw = null;         // Toonces admin password
$firstName = null;  // toonces user first name
$lastName = null;   // toonces user last name
$nickname = null;   // toonces user nickname
$phpHost = null;    // PHP host IP/domain


// Other primitives
$args = array();

// Objects
$conn = null;

// **** PROCEDURAL EXECUTION ****
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
$phpHost = $args['phphost'];

// Check for required arguments
$allParamsPresent = isset($mh) && isset($mu) && isset($mp) && isset($tup) && isset($email) && isset($pw) && isset($firstName) && isset($lastName) && isset($nickname) && isset($phpHost);

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

// OK so far? 
// Call the build frunction
setupTooncesDatabase($conn, $tup, $email, $pw, $firstName, $lastName, $nickname, $phpHost);
