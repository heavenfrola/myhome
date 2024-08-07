<?php

/**
 * 카카오페이 구매 전체 상품 목록 API
 **/

use Wing\API\Kakao\KakaoTalkPay;

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/wingPos.lib.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
$talkpay->compareShopKey($_GET['shopKey']);

// 페이징
$page = $_GET['continuationToken'];
if (empty($page) == true) {
    $page = 1;
}
$size = $_GET['size'];
if (empty($size) == true || $size > 100) {
    $size =  100;
}
$start = ($page-1)*$size;
$size++;
$next_page = '';

$w = '';
if($cfg['use_prd_perm'] == 'Y') {
    $w .= " and perm_lst='Y'";
}

// 상품 목록
$productIds = array();
$res = $pdo->iterator("select hash from {$tbl['product']} where prd_type='1' and stat in (2, 3) $w order by no desc limit $start, $size");
foreach ($res as $key => $prd) {
    if (($key+1) >= $size) {
        $next_page = ($page+1);
        break;
    }
    $productIds[] = $prd['hash'];
}

$list = array(
    'shopKey' => $scfg->get('talkpay_ShopKey'),
    'productIds' => $productIds,
    'continuationToken' => $next_page,
);

header('Content-type: application/json');
echo json_encode($list);