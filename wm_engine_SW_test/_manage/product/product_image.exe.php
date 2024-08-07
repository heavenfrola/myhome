<?php

/**
 * 상품 이미지 관리
 **/

$exec = $_GET['exec'];

// 성인인증 상품 대체 섬네일 삭제
if ($exec == 'delThumbAdult') {
    $updir = '/_data/_default/prd/';
    deleteAttachFile($updir, $cfg['thumb_adult']);
    $scfg->remove('thumb_adult');
    exit;
}