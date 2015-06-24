<?php

/**
 * Class url
 */

class url {

/**
 * CLASS PROPERTIES AND METHODS
 */
	private function __construct() {
	}

/**
 * STATIC PROPERTIES AND METHODS
 */
	private static $url_config;

	public static function init($url_config) {
		$valid_keys = array(
			"rewrite",
			"suffix"
		);
		foreach($valid_keys as $valid_key) {
			if (!array_key_exists($valid_key, $url_config)) {
				throw new Exception("Invalid URL Settings");
			}
		}
		self::$url_config = $url_config;
	}

	public static function base_url($file_name = "") {
		if ($file_name != "") {
			return str_replace("index.php", "", $_SERVER['PHP_SELF']) . $file_name;
		} else {
			if (self::$url_config['rewrite'] == "true") {
				return str_replace("index.php", "", $_SERVER['PHP_SELF']);
			} else {
				return str_replace("index.php", "", $_SERVER['PHP_SELF']) . "?q=";			
			}
		}
	}

	public static function site_url($file_name = "") {
		if ($file_name != "") {
			$file_name = substr($file_name, -1, 1) == "/" ?
				substr($file_name, 0, -1) : $file_name;
			return self::base_url() . $file_name . self::$url_config['suffix'];
		}
	}

	public static function remove_suffix($url) {
		if (substr($url, -strlen(self::$url_config['suffix'])) == self::$url_config['suffix']) {
			return substr($url, 0, -strlen(self::$url_config['suffix']));
		}
		return $url;
	}
}

