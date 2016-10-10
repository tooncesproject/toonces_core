<?php

// Unit test for Enumeration / InputTypes classes
// Paul Anderson, 2016-10-09

include 'config.php';
require_once LIBPATH.'toonces.php';

$stringValue = 'text';
$ordinalValue = 19;

// test getOrdinal method
echo 'getOrdinal'.PHP_EOL;
echo Enumeration::getOrdinal($stringValue, 'InputTypes').PHP_EOL;

// test getString method
echo 'getString'.PHP_EOL;
echo Enumeration::getString($ordinalValue, 'InputTypes').PHP_EOL;

// test validateString method: negative
echo 'validateString negative'.PHP_EOL;
$vsResult = Enumeration::validateString('foo', 'InputTypes');
if ($vsResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateString positive'.PHP_EOL;
$vspResult = Enumeration::validateString('text', 'InputTypes');
if (!$vspResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateOrdinal negative'.PHP_EOL;
$voResult = Enumeration::validateOrdinal(666, 'InputTypes');
if ($voResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateOrdinal positive'.PHP_EOL;
$vopResult = Enumeration::validateOrdinal(19, 'InputTypes');
if (!$vopResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}