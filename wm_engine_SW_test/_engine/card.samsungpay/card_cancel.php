<?php

/**
 * 삼성페이 환불처리
 */

header("Pragma: No-Cache");

include $engine_dir."/_engine/card.samsungpay/inc/function.php";

if ($cno == '') {
    msg('환불 정보가 없습니다.');
}

$req_data = array();
if ($price == 0 && $card_cancel_type == '1') { // 전체취소
    $price = intval($card['wm_price']);
    $canceltype = 'C'; // C: 전체취소, P: 부분취소
} else {
    $price = intval($_GET['price']);
    $canceltype = 'P'; // C: 전체취소, P: 부분취소
}

// CP 정보
$req_data["CPID"] = $scfg->get('samsungpay_id');

// 취소 정보
$req_data["TID"] = $card['tno'];
$req_data["CANCELTYPE"] = $canceltype;
$req_data["AMOUNT"] = $price;

// 기본 정보
$req_data["TXTYPE"] = "CANCEL"; // (고정값. 수정하지 마세요)
$req_data["SERVICETYPE"] = "ISPAY"; // (고정값. 수정하지 마세요)

$res_data = CallCredit($req_data, false);

$res_data['RETURNMSG'] = strToEncoding($res_data['RETURNMSG']);

$newPrice = $card['wm_price'] - $price;

if ($res_data["RETURNCODE"] === '00000') {
    $cstat = ($canceltype == 'C' || $newPrice == 0) ? '3' : '2';
    $pdo->query("update {$tbl['card']} set stat = '$cstat', wm_price = '$newPrice' where no = '{$card['no']}' limit 1");
    $log_stat = 2;
    $ems = '거래취소성공!';
} else {
    $log_stat = 1;
    $ems = '거래취소실패!';
    $ems .= "\\n".$res_data['RETURNMSG'];
}

$pdo->query("
    insert into {$tbl['card_cc_log']} 
        (cno, stat, ono, price, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
    values 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", array(
        $card['no'], $log_stat, $card['wm_ono'], $price, $card['tno'], $res_data['RETURNCODE'], addslashes($res_data['RETURNMSG']), $admin['admin_id'], $admin['no'], $_SERVER['REMOTE_ADDR'], $now)
);

// 주문서 처리와 함께 카드 취소
if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
    $card_cancel_result = ($res_data['RETURNCODE'] === '00000') ? 'success' : $res_data['RETURNMSG'];
    return;
}

msg($ems, "reload", "parent");