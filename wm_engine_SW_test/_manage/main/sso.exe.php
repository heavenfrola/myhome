<?php

/**
 * 쇼핑몰간 관리자 모드 이동
 **/

use Wing\HTTP\CurlConnection;

$api = new CurlConnection(
    $_GET['domain'].'/main/exec.php?'.http_build_query(array(
        'exec_file' => 'common/ssoLogin2.php',
        'exec' => 'getToken',
        'ret_url' => $_GET['ret_url'],
        'admin_no' => $admin['no'],
        'skey' => session_id(),
        'site_key' => trim($_site_key_file_info[2]),
        'urlfix' => 'Y'
    ))
);
$api->exec();
$ret = json_decode($api->getResult());

if (is_object($ret) == false) {
    msg('접속할 사이트의 스마트윙 버전이 낮습니다.\\n업데이트 후 이용해주세요.', 'back');
}

if ($ret->result != 'success' || !$ret->ssoKey) {
    msg('사이트 인증이 실패되었습니다.', 'back');
}

header('Location: '.
$_GET['domain'].'/main/exec.php?'.http_build_query(array(
    'exec_file' => 'common/ssoLogin2.php',
    'admin_no' => $admin['no'],
    'skey' => session_id(),
    'ssoKey' => $ret->ssoKey,
    'ret_url' => $_GET['ret_url'],
    'body' => $_GET['nbody'],
	'site_key' => trim($_site_key_file_info[2])
)));

?>