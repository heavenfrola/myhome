<?php

/**
 * Catch Fatal Error
 */

use Wing\common\ErrorReport;

function shutdownHandler()
{
    $error = error_get_last();
    if (!in_array($error['type'], array(E_WARNING, E_NOTICE, E_DEPRECATED))) {
        $engine_dir = str_replace(DIRECTORY_SEPARATOR, '/', __ENGINE_DIR__);
        $error['file'] = str_replace(DIRECTORY_SEPARATOR, '/', $error['file']);

        $type = getErrorName($error['type']);
        $error_message = str_replace($engine_dir, '', $error['message']);

        ErrorReport::__report(
            $type,
            $error_message,
            $error['type'],
            array(
                array('line' => $error['line'], 'file' => $error['file'])
            )
        );

        exit(
            "<br><strong>$type</strong> : " .
            $error_message . " in " .
            "<strong>{$error['file']}</strong> " .
            "on line " . $error['line']
        );
    }
}

function getErrorName($code)
{
    static $error_names = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    );
    return $error_names[$code];
}

error_reporting(0);
register_shutdown_function('shutdownHandler');