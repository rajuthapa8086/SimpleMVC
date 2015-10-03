<?php

# DIRECTORY SETTINGS
define('DS', DIRECTORY_SEPARATOR);
define('APP', "private" . DS . "app" . DS);
define('SYS', "private" . DS . "sys" . DS);

# DEBUB SETTING
define('DEBUG', false);

# INCLUDE FILES TO RUN
require_once APP . 'config' . DS . 'app_config.php';
require_once SYS . 'main.php';

# RUN THE PROGRAM
main::run();