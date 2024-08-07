<?php

/**
 * 윙스토어를 통한 카카오 알림톡 설정 삭제 수신
 **/

include_once $engine_dir.'/_engine/include/common.lib.php';

$keycode = (isset($_GET['keycode']) == true) ? $_GET['keycode'] : null;

if (empty($keycode) == true) {
    exit(json_encode(array(
        'result' => 'no keycode'
    )));
}

if (strcmp(trim($_site_key_file_info[2]), $keycode) !== 0) {
    exit(json_encode(array(
        'result' => 'wrong keycode'
    )));
}

if ($scfg->comp('alimtalk_profile_key') == false) {
    exit(json_encode(array(
        'result' => 'Kakao alimtalk is Not set'
    )));
}

$pdo->query("delete from {$tbl['config']} where name in ('alimtalk_profile_key', 'alimtalk_id')");
$pdo->query("update {$tbl['alimtalk_template']} set reg_status='RMVD'");
$pdo->query("update {$tbl['sms_case']} set alimtalk_code=''");

exit(json_encode(array(
    'result' => 'success'
)));

?>