<?php

/**
 * 카카오싱크 연동 설정
 **/

use Wing\API\Kakao\KakaoSync;
use Wing\API\Kakao\KakaoTalkChannel;

include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';

// 콜백 확인
if ($_POST['exec'] == 'check_callback') {
    if ($cfg['kakao_login_use'] == 'S') {
        exit('true');
    }
    exit('false');
}

/**
 * 카카오채널 고객 파일 리스트 확인
 **/
if ($_POST['exec'] == 'getTalkchannel') {
    $kc = new KakaoTalkChannel();
    $ret = $kc->getFiles();

    $files = array();
    foreach ($ret as $val) {
        $files[] = array(
            'id' => $val->file_id,
            'name' => $val->file_name,
            'status' => ($val->status == 'USING') ? '' : 'disabled'
        );
    }

    header('Content-type: application/json');
    exit(json_encode($files));
}

// 카카오 로그인용 파일 생성
if ($_POST['kakao_login_use'] == 'S' || $_POST['kakao_login_use'] == 'Y') {
    makeFullDir('_data/compare/kakao');

    fwriteTo(
        '_data/compare/kakao/kakao_login_auth.php',
        "<?php\ninclude '../../../_config/set.php';\ninclude \$engine_dir.'/_engine/promotion/kakao_login_auth.exe.php';\n?>",
        'w'
    );
}

// 카카오 싱크 파일 생성
if ($_POST['kakao_login_use'] == 'S') {
    // 사업자 정보 API
    fwriteTo(
        '_data/compare/kakao/sync_info.php',
        "<?php\ninclude '../../../_config/set.php';\ninclude \$engine_dir.'/_engine/promotion/kakaosync_info.exe.php';\n?>",
        'w'
    );

    // 및 콜백 API
    fwriteTo(
        '_data/compare/kakao/sync_callback.php',
        "<?php\ninclude '../../../_config/set.php';\ninclude \$engine_dir.'/_engine/promotion/kakaosync_callback.exe.php';\n?>",
        'w'
    );
}

// 카카오싱크 간편 가입창 오픈
if ($_POST['kakao_login_use'] == 'S') {
    if ($_FILES['kakao_site_logo']['size'] > 0) {
        include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';

        if (getimagesize($_FILES['kakao_site_logo']['tmp_name']) == false) {
            msg('이미지파일만 업로드할수 있습니다.');
        }

        if ($scfg->comp('kakao_site_logo') == true) {
            deleteAttachFile('_data/config', $scfg->get('kakao_site_logo'));
        }

        $filename = md5(time());
        uploadFile($_FILES['kakao_site_logo'], $filename, '_data/config', 'png|jpg|jpeg|gif');

        $_POST['kakao_site_logo'] = $filename.'.'.getExt($_FILES['kakao_site_logo']['name']);
    } else {
        if ($scfg->comp('kakao_site_logo') == false) {
            msg('사이트 로고를 설정해주세요.');
        }
    }

    $scfg->import($_POST);

    if (empty($cfg['kakao_rest_api']) == true) {
        unset($_POST['kakao_login_use']);

        if (empty($_POST['kakaoSync_StoreKey']) == true) {
            $_POST['kakaoSync_StoreKey'] = md5(time());
        }

        $wec = new weagleEyeClient($_we, 'Etc');
        $wec->call('setExternalService', array(
            'service_name' => $_POST['kakao_login_use'],
            'use_yn' => 'N',
            'root_url' => $root_url,
            'extradata' => $_POST['kakaoSync_StoreKey']
        ));

        $kkosync = new KakaoSync($_POST['kakaoSync_StoreKey'], $_POST['kakao_rest_api']);
        $url = $kkosync->getDialogUrl();

        javac("
        var sync = window.open(
            '$url',
            'kakao_sync',
            'status=Y, width=640px, height=750px, top=10px, left=200px'
        );
        if (!sync) {
            window.alert('팝업창이 차단되어있습니다. 팝업창 허용 후 다시 진행해주세요.');
            parent.removeLoading();
        } else {
            parent.removeLoading();
        }
        ");
        $no_reload_config = true;
    } else {
        $kkosync = new KakaoSync($_POST['kakaoSync_StoreKey'], $_POST['kakao_rest_api']);
        $kkosync->modification('edit');
    }
}

// 카카오싱크 OFF
if ($_POST['kakao_login_use'] == 'N') {
    if ($scfg->comp('kakao_login_use', 'S') == true) {
        $wec = new weagleEyeClient($_we, 'Etc');
        $wec->call('setExternalService', array(
            'service_name' => $_POST['kakao_login_use'],
            'use_yn' => 'N',
            'root_url' => $root_url,
            'extradata' => ''
        ));

        $kkosync = new KakaoSync($cfg['kakaoSync_StoreKey'], $cfg['kakao_rest_api']);
        $kkosync->modification('disconnect');

        $_POST['kakaoSync_StoreKey'] = '';
        $_POST['kakao_rest_api'] = '';
        $_POST['kakao_sns_id'] = '';
    }
}

require __ENGINE_DIR__.'/_manage/config/config.exe.php';

?>