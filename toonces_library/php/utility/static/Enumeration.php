<?php
/*
 * Enumeration
 * Initial Commit: Paul Anderson, 10/9/2016
 * 
 * Abstract class governing enumerations, as PHP has none...
 * 
 * 
 */

require_once LIBPATH.'php/toonces.php';

class Enumeration
{


	public static function getOrdinal($paramString,$enumClass) {
		$vars = get_class_vars($enumClass);
		return array_search($paramString, $vars['enum']);
	}

	public static function getString($paramOrdinal,$enumClass) {
		$vars = get_class_vars($enumClass);
		return $vars['enum'][$paramOrdinal];
	}

	public static function validateString($paramString, $enumClass) {
		$vars = get_class_vars($enumClass);
		return in_array($paramString, $vars['enum']);
	}

	public static function validateOrdinal($paramOrdinal, $enumClass) {
		$vars = get_class_vars($enumClass);
		return array_key_exists($paramOrdinal, $vars['enum']);
	}
}
