<?php

// Unit test for Enumeration / EnumInputTypes classes
// Paul Anderson, 2016-10-09

include 'config.php';
require_once LIBPATH.'php/toonces.php';

$stringValue = 'text';
$ordinalValue = 19;

// test getOrdinal method
echo 'getOrdinal'.PHP_EOL;
echo Enumeration::getOrdinal($stringValue, 'EnumInputTypes').PHP_EOL;

// test getString method
echo 'getString'.PHP_EOL;
echo Enumeration::getString($ordinalValue, 'EnumInputTypes').PHP_EOL;

// test validateString method: negative
echo 'validateString negative'.PHP_EOL;
$vsResult = Enumeration::validateString('foo', 'EnumInputTypes');
if ($vsResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateString positive'.PHP_EOL;
$vspResult = Enumeration::validateString('text', 'EnumInputTypes');
if (!$vspResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateOrdinal negative'.PHP_EOL;
$voResult = Enumeration::validateOrdinal(666, 'EnumInputTypes');
if ($voResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}

echo 'validateOrdinal positive'.PHP_EOL;
$vopResult = Enumeration::validateOrdinal(19, 'EnumInputTypes');
if (!$vopResult) {
	echo 'error'.PHP_EOL;
} else {
	echo 'test passes'.PHP_EOL;
}