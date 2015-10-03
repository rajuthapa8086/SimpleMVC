<?php

/**
 * PHP built-in function call set_exception_handler
 */
set_error_handler("myErrorHandler");
set_exception_handler("exception_handler");

/**
 * Function exception_handler
 */
function exception_handler($e) {
    if ($e instanceof MySQLiException) {
        if ($e->err_type == "connect_error") {
            _die(
                "Database Error!",
                "<p>Sorry, Could not connect to your database server.</p>" .
                sprintf(
                    "<p><strong>MySQL Error:</strong> %s.</p>",
                    $e->getMessage()
                )
            );
        } else if ($e->err_type == "sql_error") {
            _die(
                "Database Error!",
                "<p>Sorry, Could not execute this page.</p>" .
                sprintf(
                    "<p><strong>MySQL Error:</strong> %s.</p>",
                    $e->getMessage()
                )
            );
        } else {
            _die(
                "Database Error!",
                sprintf(
                    "<p><strong>MySQL Error:</strong> %s.</p>",
                    $e->getMessage()
                )
            );
        }
    } else if ($e instanceof NotFoundException) {
        if (file_exists(APP . 'views' . DS . 'errors' . DS . '404.phtml')) {
            header('HTTP/1.0 404 Not Found');
            ob_start();
            include APP . 'views' . DS . 'errors' . DS . '404.phtml';
            $content = ob_get_contents();
            ob_clean();
            echo $content;
            exit;
        } else {
            _die(
                "404 Page Not Found!",
                sprintf(
                    "<p>Page you requested was not found</p>"
                )
            );
        }
    } else {
        _die(
            "Error!",
            sprintf(
                "<p><strong>Error:</strong> %s.</p>",
                $e->getMessage()
            )
        );
    }
}

/**
 * Function error_handler
 */

function myErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    switch ($errno) {
        case E_USER_ERROR:
            _die(
                "Error Occured!",
                sprintf(
                    "<p><strong>Fatal Error: </strong>[$errno] $errstr.</p>" .
                    "<p><strong>Line Number: $errline</p>" .
                    "<p><strong>File Name: $errfile</p>"
                )
            );
            break;

        case E_USER_WARNING:
            _die(
                "Warning!",
                sprintf(
                    "<p><strong>Warning: </strong>[$errno] $errstr.</p>" .
                    "<p><strong>Line Number: $errline</p>" .
                    "<p><strong>File Name: $errfile</p>"
                ),
                false
            );
            break;

        case E_USER_NOTICE:
            echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
            _die(
                "Notice!",
                sprintf(
                    "<p><strong>Notice: </strong>[$errno] $errstr.</p>" .
                    "<p><strong>Line Number: $errline</p>" .
                    "<p><strong>File Name: $errfile</p>"
                ),
                false
            );
            break;

        default:
            _die(
                "Error!",
                sprintf(
                    "<p><strong>Error: </strong>[$errno] $errstr.</p>" .
                    "<p><strong>Line Number: $errline</p>" .
                    "<p><strong>File Name: $errfile</p>"
                )
            );
            break;
    }

    /* Don't execute PHP internal error handler */
    return false;
}

/**
 * Function _die
 */
function _die($title, $body, $die = true) {
    $template_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . "template.error.html";
    $content       = "<title>_TITLE_</title>\n";
    $content .= "<h1>_TITLE_</h1>\n";
    $content .= "_BODY_\n";
    if (file_exists($template_file)) {
        $content = file_get_contents($template_file);
    }
    if ($die) {
        die(
            str_replace(
                "_BODY_",
                $body,
                str_replace(
                    "_TITLE_",
                    $title,
                    $content
                )
            )
        );
    } else {
        echo (
            str_replace(
                "_BODY_",
                $body,
                str_replace(
                    "_TITLE_",
                    $title,
                    $content
                )
            )
        );
    }
}

/**
 * Class MySQLiException
 */
class MySQLiException extends Exception {

/**
 * CLASS PROPERTIES AND METHODS
 */

    public $err_type;

    public function __construct($err_type, $obj) {
        $this->err_type = $err_type;
        switch ($err_type) {
            case "connect_error":
                $this->message = sprintf(
                    "(%d) %s",
                    mysqli_connect_errno(),
                    mysqli_connect_error()
                );
                break;
            case "sql_error":
                $this->message = sprintf(
                    "(%d) %s",
                    $obj->errno,
                    $obj->error
                );
                break;
            default:
                $this->message = "Unknown Error";
                break;
        }
    }
/**
 * STATIC PROPERTIES AND METHODS
 */
}

/**
 * Class NotFoundException
 */
class NotFoundException extends Exception {
/**
 * CLASS PROPERTIES AND METHODS
 */

/**
 * STATIC PROPERTIES AND METHODS
 */

}
