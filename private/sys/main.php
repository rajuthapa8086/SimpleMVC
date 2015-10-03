<?php

require_once SYS . 'core' . DS . 'exceptions.php';

/**
 * Class main
 */

class main {

/**
 * CLASS PROPERTIES AND METHODS
 */

    /**
     * STATIC PROPERTIES AND METHODS
     */

    private static function autoload($class) {
        if (file_exists(SYS . 'core' . DS . $class . '.php')) {
            require_once SYS . 'core' . DS . $class . '.php';
        }
    }

    public static function run() {
        spl_autoload_register(__CLASS__ . "::" . 'autoload');

        if (app_config::$use_db == true) {
            db::set_db_config(
                app_config::$db
            );
        }

        url::init(
            app_config::$url
        );

        router::route(
            app_config::$router,
            app_config::$re_route
        );

    }

}
