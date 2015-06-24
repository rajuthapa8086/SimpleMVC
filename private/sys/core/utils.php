<?php

/**
 * Class utils
 */
 
class utils {

/**
 * CLASS PROPERTIES AND METHODS
 */
	private function __construct() {
	}
	
/**
 * STATIC PROPERTIES AND METHODS
 */

	public static function key_exists($keys, $arr) {
		if (is_array($keys) && is_array($arr)) {
			foreach($keys as $key) {
				if (!array_key_exists($key, $arr)) {
					return 0;
				}
			}
			return 1;
		}
		return -1;
	}
	
	public static function is_in_array($arr_child, $arr_parent) {
		foreach($arr_child as $arr) {
			if (!in_array($arr, $arr_parent)) {
				return false;
			}
		}
		return true;
	}

	public static  function array_to_object($arr, &$obj) {
		if (is_array($arr) && is_object($obj)) {
			foreach($arr as $key => $value) {
				$obj->$key = $value;
			}
		}
	}
	
	public static function object_to_array($obj) {
		if (is_object($obj)) {
			return get_object_vars($obj);
		}
		return array();
	}
	
	public static function text_array($arr) {
		$return = null;
		if (is_array($arr) && !empty($arr)) {
			foreach($arr as $key => $value) {
				if (is_array($value)) {
					$return .= " + $key =>\n";
					$return .= self::text_array($value);
				} else {
					$return .= "- $key => " . $value . "\n";
				}
			}
		}
		return $return;
	}

}
