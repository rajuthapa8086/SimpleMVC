<?php

class app_config {

    public static $url = array(
        # Currently rewrite true is required for other $_GET[] which comes after $_GET['q']
        'rewrite' => "false",
        'suffix'  => "",
    );

    public static $router = array(
        'controller' => "home",
        'action'     => "index",
    );

    public static $re_route = array();

    public static $db = array(
        #'host'        => 'YOUR_DB_HOSTNAME',
        #'user'        => 'YOUR_DB_USERNAME',
        #'pass'        => 'YOUR_DB_PASSWORD',
        #'name'        => 'YOUR_DB_NAME',
        #'tpfx'        => 'YOUR_DB_TABLE_PREFIX'
    );

    public static $use_db = false;

}