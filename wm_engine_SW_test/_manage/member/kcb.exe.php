<?php

/**
 * KCB 본인 인증 설정
 */

$cpcd = trim($_POST['kcb_cpcd']);
if ($_POST['use_kcb'] != 'Y' && $_POST['use_kcb'] != 'N') {
    jsonReturn([
        'status' => 'error',
        'message' => '서비스 사용 여부를 입력해주세요.'
    ]);    
}

if ($_POST['use_kcb'] == 'Y') {
    if (!$cpcd) {
        jsonReturn([
            'status' => 'error',
            'message' => '회원사 코드를 입력해주세요.'
        ]);
    }

    // 인증 정보 테이블 생성
    if (!isTable($tbl['member_cert'])) {
        require_once __ENGINE_DIR__ . '/_config/tbl_schema.php';
        if (!$pdo->query($tbl_schema['member_cert'])) {
            jsonReturn([
                'status' => 'error',
                'message' => '데이터베이스 설정 중 오류가 발생하였습니다.' . $pdo->geterror()
            ]);
        }
        $pdo->query("alter table {$tbl['product']} add adult enum('Y', 'N') default 'N'");
    }

    // 로그 폴더 생성
    if (!dirname($root_dir . '/_data/okname/log')) {
        makeFullDir('_data/okname/log');
    }
}

// 라이센스 파일 업로드
$file = $_FILES['kcb_license'];
if ($file['size'] > 0) {
    require_once __ENGINE_DIR__ . '/_engine/include/file.lib.php';
    makeFullDir('_data/okname');
    $r = uploadFile($file, 'license', '_data/okname', 'dat');
}

$scfg->import([
   'use_kcb' => $_POST['use_kcb'],
   'kcb_cpcd' => $cpcd 
]);

jsonReturn([
    'status' => 'success'
]);