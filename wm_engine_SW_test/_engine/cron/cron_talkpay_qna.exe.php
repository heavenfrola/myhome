<?php

/**
 * 카카오페이 구매 주문 수집 콜백
 **/

use Wing\API\Kakao\KakaoTalkPay;

include_once $engine_dir.'/_engine/include/common.lib.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
$talkpay->compareShopKey($_GET['shopKey']);

// 주문 수집
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
    $res = $talkpay->getQuestions($startDateTime, $endDateTime, $size, $continuationToken);
    foreach ($res->content as $key => $qna) {
        $questionId = 'talkpay'.$qna->questionId;
        $category = $talkpay->getQuestionCategory($qna->category);
        $reg_date = $talkpay->parseDateFormat($qna->createdDateTime, true);
        $title = addslashes($qna->source);
        $content = addslashes($qna->contents);
        $ono = $qna->orderId;
        $answer_ok = (isset($qna->answer->answerId) == true) ? 'Y' : 'N';
        $answer = addslashes($qna->answer->contents);
        $answer_date = $talkpay->parseDateFormat($qna->answer->answeredDateTime, true);
        $answerId = $qna->answer->answerId;
        $name = '(카카오페이 구매)';

        // 삭제
        if ($qna->deleted == 'true') {
			$data = $pdo->assoc("select updir, upfile1, upfile2 from {$tbl['qna']} where external_id='$questionId'");
			deletePrdImage($data, 1, 2);

            $pdo->query("delete from {$tbl['qna']} where external_id='$questionId'");
            if ($pdo->lastRowCount() > 0) {
                $total_remove++;
            }
            continue;
        }

		$exists = $pdo->assoc("select no, answer_date from {$tbl['qna']} where external_id='$questionId'");
		if($exists['no'] > 0) {
			$pdo->query("
                update {$tbl['qna']} set
                    title='$title', cate='$category', content='$content',
                    answer='$answer', answer_ok='$answer_ok', answer_date='$answer_date',
                    external_answer_id='$answerId', secret='Y'
                where external_id='$questionId'
            ");

            if ($pdo->lastRowCount() > 0) {
                $total_update++;
            }
		} else {
            // 관련 주문서
            if ($ono) {
    			$ord = $pdo->assoc("select date1, buyer_name from {$tbl['order']} where ono='$ono'");
                $buy_date = $ord['date1'];
                $name = $ord['buyer_name'];
            } else {
                $buy_date = 0;
            }

            // 관련 상품
            $pno = 0;
            if (is_array($qna->orderProducts) == true && count($qna->orderProducts) == 1) {
                $pno = $qna->orderProducts[0]->productId;
                $pno = $pdo->row("select no from {$tbl['product']} where hash=?", array($pno));
            }

            $pwd = sql_password($ono.$reg_date);

            // 첨부파일
            $asql1 = $asql2 = $asql3 = '';
            if (count($qna->addOnFiles) > 0) {
                $updir = '_data/qna/'.date('Ym/d');
                $asql1 .= ", updir";
                $asql2 .= ", '$updir'";

                foreach ($qna->addOnFiles as $file_idx => $file) {
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
				insert into {$tbl['qna']}
				(member_id, name, pwd, title, cate, content, reg_date, answer, answer_ok, answer_date, buy_date, external_id, external_answer_id, secret, pno $asql1)
				values
				('$member_id', '$name', '$pwd', '$title', '$category', '$content', '$reg_date', '$answer', '$answer_ok', '$answer_date', '$buy_date', '$questionId', '$answerId', 'Y', '$pno' $asql2)
			");

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
    'qna' => array(
        'start_date' => $startDateTime,
        'finish_date' => $endDateTime,
        'fetch' => $total_fetch,
        'insert' => $total_insert,
        'update' => $total_update,
        'removed' => $total_remove
    )
)));