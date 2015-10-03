<?php

/**
 * Class router
 */

class router {

/**
 * CLASS PROPERTIES AND METHODS
 */
    private function __construct() {
    }

/**
 * STATIC PROPERTIES AND METHODS
 */

    private static function get_url_vars() {
        $return = array();
        if (isset($_GET['q']) && $_GET['q'] != "") {
            $qs      = $_GET['q'];
            $qs      = str_replace("\\", "/", $qs);
            $qs      = str_replace(" ", "_", $qs);
            $qs      = url::remove_suffix($qs);
            $explode = explode("/", $qs);
            foreach ($explode as $ex) {
                if ($ex != "") {
                    $return[] = strtolower($ex);
                }
            }
        }
        return $return;
    }

    private static function get_router_vars($config) {
        $return = array();
        if (!utils::key_exists(array("controller", "action"), $config)) {
            throw new Exception("Invalid Configuration.");
        }
        $url_vars       = self::get_url_vars();
        $count_url_vars = count($url_vars);
        if ($count_url_vars == 0) {
            $return['controller'] = $config['controller'];
            $return['action']     = $config['action'];
            $return['fnc_args']   = array();
        }
        if ($count_url_vars == 1) {
            $return['controller'] = $url_vars[0];
            $return['action']     = $config['action'];
            $return['fnc_args']   = array();
        }
        if ($count_url_vars == 2) {
            $return['controller'] = $url_vars[0];
            $return['action']     = $url_vars[1];
            $return['fnc_args']   = array();
        }
        if ($count_url_vars > 2) {
            $return['controller'] = $url_vars[0];
            $return['action']     = $url_vars[1];
            $return['fnc_args']   = array();
            for ($i = 2; $i < $count_url_vars; $i++) {
                $return['fnc_args'][] = $url_vars[$i];
            }
        }
        if (isset($_GET)) {
            $return['gets'] = array();
            foreach ($_GET as $key => $value) {
                if ($key != "q") {
                    $return['gets'][$key] = $value;
                }
            }
        }
        return $return;
    }

    public static function route($config, $re_route) {
        $router_vars = self::get_router_vars($config);
        if (count($router_vars) > 1) {
            if (utils::key_exists(array($router_vars['controller']), $re_route)) {
                if (in_array($router_vars['action'], $re_route[$router_vars['controller']])) {
                    $router_vars['controller'] = $router_vars['action'];
                    $router_vars['action']     = !empty($router_vars['fnc_args']) ? $router_vars['fnc_args'][0] : $config['action'];
                    if (count($router_vars['fnc_args']) > 1) {
                        array_shift($router_vars['fnc_args']);
                    } else {
                        $router_vars['fnc_args'] = array();
                    }
                }
            }
        }

        if ($router_vars['controller'] == "base") {
            $router_vars['controller'] = $config['class'];
        }

        if (!file_exists(APP . 'controllers' . DS . $router_vars['controller'] . '.php')) {
            throw new NotFoundException();
        }

        require_once APP . 'controllers' . DS . $router_vars['controller'] . '.php';

        if (!class_exists($router_vars['controller'])) {
            throw new NotFoundException();
        }

        $instance = new $router_vars['controller'];

        if (!method_exists($instance, $router_vars['action'])) {
            throw new NotFoundException();
        }

        $instance->$router_vars['action']($router_vars['fnc_args'], $router_vars['gets']);
        if (DEBUG) {
            debug::set("router", "router", $router_vars);
            debug::set("session", "session", session::get_all());
            debug::show();
        }
    }

}
