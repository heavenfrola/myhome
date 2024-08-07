<?php

/**
 * jsonp, 현재 접속한 회원 아이디 출력
 **/

$urlfix = 'Y';
include_once $engine_dir.'/_engine/include/common.lib.php';

$timestamp = $_GET['timestamp'];
$keycode = $_GET['keycode'];

if (trim($_site_key_file_info[2]) != $keycode) {
    exit('callback'.$timestamp.'('.json_encode(array(
        'message' => 'access denied'
    )).')');
}

exit('callback'.$timestamp.'('.json_encode(array(
    'member_no' => $member['no'],
    'member_id' => $member['member_id'],
)).')');
