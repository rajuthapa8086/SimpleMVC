<?php

/**
 * Class session
 */

class session {

/**
 * CLASS PROPERTIES AND METHODS
 */
    private function __construct() {
    }

/**
 * STATIC PROPERTIES AND METHODS
 */
    public static function set($key, $value) {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (array_key_exists($key, $_SESSION)) {
            self::destroy($key);
        }
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return false;
    }

    public static function get_all() {
        if (!isset($_SESSION)) {
            session_start();
        }
        return $_SESSION;
    }

    public static function destroy($key) {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (array_key_exists($key, $_SESSION)) {
            $_SESSION[$key] = null;
            unset($_SESSION[$key]);
        }
    }

}
