<?php

/**
 * 카카오페이 구매 전체 상품 목록 API
 **/

use Wing\API\Kakao\KakaoTalkPay;

include_once $engine_dir.'/_engine/include/common.lib.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
//$talkpay->compareShopKey($_GET['shopKey']);

// 상품 상세
$result = array(
    'shopKey' => $scfg->get('talkpay_ShopKey'),
    'products' => array()
);

// 상품 정보 로딩
$productIds = explode(',', $_GET['productIds']);
$reviewMaxCount = numberOnly($_GET['reviewMaxCount']);
if (!$reviewMaxCount) {
    $reviewMaxCount = 10;
}

$list = array();

foreach ($productIds as $productId) {
    $res = $pdo->iterator("select no, hash, rev_avg, rev_cnt from {$tbl['product']} where hash='$productId'");

    foreach ($res as $data) {
        $reviews = array();

        $res2 = $pdo->iterator("select * from {$tbl['review']} where pno='{$data['no']}' order by no desc limit $reviewMaxCount");
        foreach ($res2 as $review) {
            // 리뷰 작성 서비스 명
            $_service_name = 'ETC';
            if (preg_match('/^talkpay/', $review['external_id']) == true) $_service_name = 'KAKAO';
            if ($review['npay'] == 'Y') $_service_name = 'NAVER';

            // 첨부파일
            $imageUrls = array();
            if ($review['upfile1']) $imageUrls[] = getListImgURL($review['updir'], $review['upfile1']);
            if ($review['upfile2']) $imageUrls[] = getListImgURL($review['updir'], $review['upfile2']);

            $reviews[] = array(
                'id' => $review['no'],
                'starPoint' => $review['rev_pt'],
                'serviceName' => $_service_name,
                'writer' => stripslashes($review['name']),
                'contents' => strip_tags(stripslashes($review['content'])),
                'imageUrls' => $imageUrls,
                'reviewUrl' => $root_url.'/shop/product_review.php?rno='.$review['no'],
                'createdDateTime' => $talkpay->convertDateFormat($review['reg_date']),
                'updatedDateTime' => $talkpay->convertDateFormat($review['reg_date']),
            );
        }

        $list[] = array(
            'productId' => $data['hash'],
            'averageStarPoint' => $data['rev_avg'],
            'totalCount' => $data['rev_cnt'],
            'url' => $root_url.'/shop/detail.php?pno='.$data['hash'],
            'reviews' => $reviews
        );
    }
}

header('Content-type: application/json');
exit(json_encode(
    array(
        'shopKey' => $scfg->get('talkpay_ShopKey'),
        'productReviews' => $list,
    ),
    (defined('JSON_PRETTY_PRINT') == true) ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES : null
));