<?php

/**
 * Class db
 */

class db {

/**
 * CLASS PROPERTIES AND METHODS
 */
    private $db_obj;

    protected function __construct() {
        $this->db_obj = @new mysqli(
            self::$db_config['host'],
            self::$db_config['user'],
            self::$db_config['pass'],
            self::$db_config['name']
        );
    }

/**
 * STATIC PROPERTIES AND METHODS
 */
    private static $db_config;
    private static $_instance;
    protected static $tbl_pfx;
    public static $sql;

    private static function instance() {
        if (self::$_instance == null) {
            self::$_instance = new db();
        }
        return self::$_instance;
    }

    public static function set_db_config($db_config) {
        if (!utils::key_exists(array("host", "user", "pass", "name", "tpfx"), $db_config)) {
            throw new Exception("Invalid Database Connection Information.");
        }
        self::$db_config = $db_config;
        self::$tbl_pfx   = $db_config['tpfx'];
    }

    public static function connect_error() {
        self::instance();
        if (mysqli_connect_error()) {
            return true;
        }
        return false;
    }

    protected static function execute($sql) {
        $return = self::instance()->db_obj->query($sql);
        if (self::instance()->db_obj->error) {
            throw new MySQLiException("sql_error", self::instance()->db_obj);
        }
        if (defined('DEBUG') && DEBUG) {
            self::$sql = $sql;
        }
        return $return;
    }

    protected static function row($sql) {
        $return = (false !== self::instance()->execute($sql)) ?
        self::execute($sql)->fetch_assoc() : null;
        if (self::instance()->db_obj->error) {
            throw new MySQLiException("sql_error", self::instance()->db_obj);
        }
        if (defined('DEBUG') && DEBUG) {
            self::$sql = $sql;
        }
        return $return;
    }

    protected static function rows($sql) {
        $return = array();
        if (null != self::row($sql)) {
            $rows = self::execute($sql);
            while ($row = $rows->fetch_assoc()) {
                $return[] = $row;
            }
        }
        if (self::instance()->db_obj->error) {
            throw new MySQLiException("sql_error", self::instance()->db_obj);
        }
        if (defined('DEBUG') && DEBUG) {
            self::$sql = $sql;
        }
        return $return;
    }

    protected static function num_rows($sql) {
        $return = (false !== self::instance()->execute($sql)) ?
        self::execute($sql)->num_rows : -1;
        if (self::instance()->db_obj->error) {
            throw new MySQLiException("sql_error", self::instance()->db_obj);
        }
        return $return;
    }

    protected static function insert_id() {
        return self::instance()->db_obj->insert_id;
    }

    protected static function affected_rows() {
        return self::instance()->db_obj->affected_rows;
    }

    protected static function esc_str($val) {
        return self::instance()->db_obj->real_escape_string($val);
    }

    public static function get_tables() {
        $rows   = self::rows("SHOW TABLES;");
        $return = array();
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $return[] = $value;
            }
        }
        return $return;
    }

    public static function timestamp() {
        return date("Y-m-d h:i:s");
    }

}
