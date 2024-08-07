<?php

/**
 * 카카오페이 구매 후기 수집 콜백
 **/

use Wing\API\Kakao\KakaoTalkPay;
use Wing\HTTP\CurlConnection;

include_once $engine_dir.'/_engine/include/common.lib.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
$talkpay->compareShopKey($_GET['shopKey']);

// 문의 수집
$continuationToken = null;
$startDateTime = $_GET['startDateTime'];
$endDateTime = $_GET['endDateTime'];
$size = $_GET['size'];
$continuationToken = $_GET['continuationToken'];

if (is_null($startDateTime) == true) {
    $startDateTime = $talkpay->convertDateFormat(
        strtotime('-2 hours')
    );
}

if (is_null($size) == true || empty($size) == true) {
    $size = 10;;
}

$total_fetch = 0;
$total_insert = 0;
$total_update = 0;
$total_remove = 0;

while (1) {
    $res = $talkpay->getReviews($startDateTime, $endDateTime, $size, $continuationToken);

    foreach ($res->content as $key => $review) {
        $reviewId = 'talkpay'.$review->reviewId;
        $reg_date = $talkpay->parseDateFormat($review->createdDateTime, true);
        $title = addslashes($review->source);
        $content = addslashes(nl2br($review->contents));
        $ono = $review->orderId;
        $rev_pt = $review->starPoint;
        $name = $review->source;
        $stat = $cfg['npay_review_stat'];

        // 삭제
        if ($review->deleted == 'true') {
			$data = $pdo->assoc("select updir, upfile1, upfile2 from {$tbl['review']} where external_id='$reviewId'");
			deletePrdImage($data, 1, 2);

            $pdo->query("delete from {$tbl['review']} where external_id='$reviewId'");
            if ($pdo->lastRowCount() > 0) {
                $total_remove++;
            }
            continue;
        }

 		$exists = $pdo->assoc("select no from {$tbl['review']} where external_id='$reviewId'");
		if($exists['no'] > 0) {
            $pdo->query("update {$tbl['review']} set title='$title', content='$content' where external_id='$reviewId'");

            if ($pdo->lastRowCount() > 0) {
                $total_update++;
            }
        } else {
            // 관련 주문서
            if ($ono) {
    			$ord = $pdo->assoc("select date1, buyer_name from {$tbl['order']} where ono='$ono'");
                $buy_date = $ord['date1'];
                //$name = $ord['buyer_name'];
            } else {
                $buy_date = 0;
            }

            // 관련 상품
            $pno = $review->orderProduct->productId;
            $pno = $pdo->row("select no from {$tbl['product']} where hash=?", array($pno));

            $pwd = sql_password($ono.$reg_date);

            // 첨부파일
            $asql1 = $asql2 = $asql3 = '';
            if (count($review->addOnFiles) > 0) {
                $updir = '_data/review/'.date('Ym/d');
                $asql1 .= ", updir";
                $asql2 .= ", '$updir'";

                foreach ($review->addOnFiles as $file_idx => $file) {
                    if ($file_idx >= 2) break;

                    $file = $talkpay->getAddOnFile($file, $updir);

                    if (is_array($file) == true) {
                        $file_idx++;
                        $asql1 .= ", upfile{$file_idx}";
                        $asql2 .= ", '$file[0]'";
                    }
                }
            }

            $pdo->query("
                insert into {$tbl['review']}
                (pno, name, pwd, rev_pt, title, content, reg_date, stat, ono, buy_date, external_id $asql1)
                values
                ('$pno', '$name', '$pwd', '$rev_pt', '$title', '$content', '$reg_date', '$stat', '$ono', '$buy_date', '$reviewId' $asql2)
            ");
            if ($pno > 0) {
                setRevPt($pno);
            }

            $total_insert++;
        }

        $total_fetch++;
    }

    // 다음 페이지 있을 경우 반복
    $continuationToken = $res->continuationToken;
    if ($res->continuationToken == null) {
        break;
    }
}

header('Content-type:application/json');
exit(json_encode(array(
    'result' => true,
    'review' => array(
        'start_date' => $dates,
        'finish_date' => $datee,
        'fetch' => $total_fetch,
        'insert' => $total_insert,
        'update' => $total_update,
        'removed' => $total_remove
    )
)));