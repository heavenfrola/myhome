<?php

/**
 * 사용자 모듈 로더
 **/

define('__MODULE_LOADER__', true);

require_once __ENGINE_DIR__.'/_engine/include/common.lib.php';
require_once __ENGINE_DIR__."/_engine/include/design.lib.php";

// page setting
$_REQUEST['accept_json'] = 'Y';
$_GET['stripheader'] = 'Y';
$cfg['design_version'] = 'V3';
$_file_name = $_tmp_file_name = $_GET['file_name'].'.php';
$_module_names = explode(',', $_GET['module_name']);
$_module_name = $_module_names[0];
$_event_type = $_GET['event_type'];
$striplayout = true;

if (preg_match('/^user([0-9]+)_list$/', $_module_name, $tmp) == true) {
    $_GET['page'.$tmp[1]] = $_GET['page'];
    unset($_GET['page']);
}

// 상위 폴더 접근 제한
if (preg_match('/\.{2,}/', $_file_name) == true) {
    jsonReturn(array(
        'status' => 'faild',
        'message' => 'Invalid file name rule'
    ));
}

// url parse
$uri = parse_url($_GET['uri']);
parse_str($uri['query'], $query);
unset($query['page'], $_GET['uri']);
$_GET = $_REQUEST = array_merge($_GET, $query);

// skin setting
$_skin['dir'] = $root_dir.'/_skin';
if($cfg['mobile_use'] == 'Y' && $_SESSION['browser_type'] == 'mobile') {
    require_once $_skin['dir'].'/mconfig.'.$_skin_ext['g'];
} else {
    require_once $_skin['dir'].'/config.'.$_skin_ext['g'];
}
$_skin['folder'] = $_skin['dir'].'/'.$design['skin'];
$_skin['url'] = $root_url.'/_skin/'.$design['skin'];
require_once $_skin['folder'].'/skin_config.'.$_skin_ext['g'];

$preprocess = preg_replace('/([^_]+)_(.*)/', '$1/$2', $_file_name);
if ($preprocess == 'shop/big_section.php' || $preprocess == 'shop/search_result.php') {
    $preprocess = 'shop/prd_list.php';
}
if ($preprocess != 'main/index.php' && $preprocess != 'board/index.php' && file_exists(__ENGINE_DIR__.'/_engine/'.$preprocess) == false) {
    jsonReturn(array(
        'status' => 'faild',
        'message' => 'file_not_exists'
    ));
}

// skin loading
ob_start();
if ($preprocess == 'main/index.php') {
    require_once __ENGINE_DIR__.'/_engine/common/skin_index.php';
} else if ($preprocess != 'main/index.php' && $preprocess != 'board/index.php') {
    require_once __ENGINE_DIR__.'/_engine/'.$preprocess;
} else if ($preprocess == 'board/index.php') {
    $no_master = true;
    require_once __ENGINE_DIR__.'/board/index.php';
}
ob_end_clean();

// check end page
$end_page = null;
if (is_object($PagingInstance) == true) {
    $end_page = $PagingInstance->end;
}

// output
$__module_result = '';
foreach ($_module_names as $_nm) {
    $__module_result .= trim($_replace_code[$_file_name][$_nm]);
}

jsonReturn(array(
    'status' => 'success',
    'html' => preg_replace('/\{{2}([^}]+)\}{2}/', '', $__module_result),
    'next_page' => ($PagingInstance->end > $_GET['page']),
    'end_page' => $end_page
));