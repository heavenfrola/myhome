<?php

/**
 * Booster API Router
 */

namespace Wing\API\Booster;

use Wing\API\Booster\Exceptions\CommonException;

/* route */
if ($_REQUEST['route']) {
    $route = explode('@', $_REQUEST['route']);
} else {
    $route = explode('/', $_SERVER['REQUEST_URI']);
    $route = array_slice($route, 3);
}

$controller_name = ucfirst($route[0]);
$action_name = $route[1];
$param = array_slice($route, 2);

// check controller
//require_once __ENGINE_DIR__.'/_engine/include/errorHandler.php';
require_once __ENGINE_DIR__.'/_engine/include/common.lib.php';

$classpath = __CLASS_DIR__.'/API/Booster/'.$controller_name.'.php';
if (!file_exists($classpath)) {
    http_response_code(404);

    jsonReturn(array(
        'result' => 'error',
        'message' => 'Controller not found'
    ));
}

// load controller

$controller_name_with_ns = 'Wing\API\Booster\\'.$controller_name;
$controller = new $controller_name_with_ns();
if (!method_exists($controller, $action_name)) {
    http_response_code(404);

    jsonReturn(array(
        'result' => 'error',
        'message' => 'Method not found'
    ));
}

// call method
try {
    $controller->$action_name();
} catch(CommonException $e) {
    $controller->out(array(
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ));
}