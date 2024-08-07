<?php

/**
 * 결제도중 품절로 자동 취소
 **/

//include_once '_config/set.php';
include_once __ENGINE_DIR__.'/_engine/include/common.lib.php';
include_once __ENGINE_DIR__.'/_engine/include/shop.lib.php';
include_once __ENGINE_DIR__.'/_manage/manage2.lib.php';

makePGLog($ono, 'order auto cancel');

$_SERVER['HTTP_REFERER'] = $root_url;
$_SERVER['REQUEST_METHOD'] = 'POST';

$repay_no = $repay_milage = $repay_member_milage = array();
$tres = $pdo->iterator("select no, total_milage, member_milage from {$tbl['order_product']} where ono='$ono'");
foreach ($tres as $tmp) {
    $repay_no[] = $tmp['no'];
    $repay_milage[] = ($tmp['total_milage']-$tmp['member_milage']);
    $repay_member_milage[] = $tmp['member_milage'];
}

// 실제 PG 취소
if ($auto_cancel_stat == '14') {
    // 결제방식에 따른 PG 취소 주소
    $pay_type = $ord['pay_type'];
    require __ENGINE_DIR__.'/_engine/order/order_paytype.exe.php';
    if ($card_pg == 'dacom') unset($pg_version);
    $cancel_path = __ENGINE_DIR__.'/_engine/card.'.$card_pg.'/'.$pg_version.'card_cancel.php';

    if (strpos(file_get_contents($cancel_path), 'duel_card_cancel') > 0) { // 모듈별 개발 여부 체크
        $auto_cancel_stat = '15';

        $card = $pdo->assoc("select * from {$tbl['card']} where wm_ono=?", array($ono));
        if ($card['stat'] == '2') {
            $cno = $card['no'];
            $price = parsePrice($ord['pay_prc']);
            $taxScopeAmount = parsePrice($card['wm_price']-$card['wm_free_price']);
            $taxExScopeAmount = parsePrice($card['wm_free_price']);
            $card_cancel_result = false;
            $duel_card_cancel = true;

            $_GET['price'] = $price;
            $_GET['taxScopeAmount'] = $taxScopeAmount;
            $_GET['taxExScopeAmount'] = $taxExScopeAmount;

            require $cancel_path;

            if ($card_cancel_result === false) {
                msg("결제도중 재고가 품절되어 주문이 취소되었으나 카드 취소 모듈을 호출하지 못했습니다.\n고객센터로 문의하세요.", $root_url, 'parent');
            }
            if ($card_cancel_result != 'success') {
                alert(php2java($card_cancel_result."\n고객센터로 문의하세요"));
                msg('', $root_url, 'parent');
            }
        }
    }
}

// 주문서 취소
$ord = $pdo->assoc("select * from {$tbl['order']} where ono='$ono'");
$_POST = array(
    'exec' => 'process',
    'stat' => $auto_cancel_stat,
    'ono' => $ono,
    'repay_no' => $repay_no,
    'repay_milage' => $repay_milage,
    'repay_member_milage' => $repay_member_milage,
    'emoney_repay' => $ord['emoney_prc'],
    'cpn_no' => $pdo->row("select no from {$tbl['coupon_download']} where ono='$ono' and stype != 5"),
    'repay_dlv_prc' => $ord['dlv_prc']-($ord['sale2_dlv']+$ord['sale4_dlv']),
    'total_repay_prc' => $ord['pay_prc'],
    'pay_type' => $ord['pay_type'],
    'reason' => '주문 중 재고 품절',
    'pno' => explode(
        ',',
        $pdo->row("select group_concat(no) from {$tbl['order_product']} where ono='$ono' and stat < 10")
    )
);

$is_counsel = true;
require __ENGINE_DIR__.'/_manage/order/order_prd_stat.exe.php';

msg('결제도중 재고가 품절되어 주문 및 결제가 자동으로 취소되었습니다.', $root_url, 'parent');