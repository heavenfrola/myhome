<?php

/**
 * 카카오싱크 설정 결과 콜백
 **/

$urlfix = 'Y';
include_once $engine_dir.'/_engine/include/common.lib.php';

$postdata = file_get_contents('php://input');
$json = json_decode($postdata);

if ($scfg->comp('kakaoSync_StoreKey', $json->storeKey) == true) {
    if ($json->data->action == 'connect' || $json->data->action == 'edit') {
        $scfg->import(array(
            'kakao_login_use' => 'S',
            'kakao_rest_api' => $json->restApiKey,
            'kakao_sns_id' => $json->jsAppKey,
        ));
    }

    if ($json->data->action == 'disconnect') {
        $scfg->remove('kakao_login_use');
        $scfg->remove('kakaoSync_StoreKey');
        $scfg->remove('kakao_rest_api');
        $scfg->remove('kakao_sns_id');

        exit('OK');
    }

    // response
    $_REQUEST['storeKey'] = $json->storeKey;
    require 'kakaosync_info.exe.php';
} else {
    exit(json_encode(array(
        'error' => 'wrong storekey'
    )));
}

?>