<?php

/**
 * 카카오 페이 구매 주문 수집 콜백
 **/

use Wing\API\Kakao\KakaoTalkPay;

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir.'/_engine/include/wingPos.lib.php';

$talkpay = new KakaoTalkPay($scfg);

// shopkey 체크
$talkpay->compareShopKey($_GET['shopKey']);


// 사전 작업
if($cfg['use_partner_delivery'] == 'Y') { // 업체별 배송 사용시
    if(!isTable($tbl['order_dlv_prc'])) {
        include_once $engine_dir.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['order_dlv_prc']);
    }
}
$erp_auto_input = 'Y'; // 재고확인 중 사용

// 주문 수집
$continuationToken = null;
$dates = (empty($_GET['dates']) == true) ? strtotime('-2 hours') : strtotime($_GET['dates']);
$dates = date('Y-m-d', $dates).'T'.date('H:i:s', $dates);
$datee = (empty($_GET['datee']) == true) ? null : strtotime($_GET['datee'])+86399;
if ($datee) {
    $datee = date('Y-m-d', $datee).'T'.date('H:i:s', $datee);
}

$total_fetch = 0;
$total_insert = 0;
$total_update = 0;

$orders = array();
while (1) {
    $res = $talkpay->getChangedOrder($dates, $datee, $continuationToken);
    if ($res->message) {
        exit(json_encode($res));
    }

    foreach ($res->content as $order) {
        $total_fetch++;

        $order_no = $order->orderId;
        $modifiedDateTime = $talkpay->parseDateFormat($order->modifiedDateTime, true);
        $id = $order->id;

        // 주문 상품 읽기
        $oprd = $pdo->assoc(
            "select no, stat, external_last_chg from {$tbl['order_product']} where external_id=?",
            array($id)
        );

        // 최신버전 SKIP
        if ($oprd['external_last_chg'] >= $modifiedDateTime && isset($_REQUEST['force_check']) == false) {
            continue;
        }

        $orders[] = $order_no;

        // 주문 상품
        $pdata = $talkpay->getOrderProduct($order->id);
        foreach ($pdata->orderProducts as $order_prd) {
            $pasql1 = $pasql2 = '';

            // 상품 읽기
            $p_afd = ($cfg['use_partner_delivery'] == 'Y') ? ', partner_no, dlv_type, partner_rate' : '';
            if($cfg['use_prd_dlvprc'] == 'Y') {
                $p_afd .= ', delivery_set';
            }
            $prd = $pdo->assoc("select no, ea_type $p_afd from {$tbl['product']} where hash='$order_prd->productId'");

            $pno = $prd['no'];
            $cono = $order_prd->id;
            $stat = $talkpay->parseStatusType($order_prd);
            $buy_ea = $order_prd->quantity;
            $sell_prc = $order_prd->unitPrice;
            $price = $order_prd->price;
            $total_prc = ($order_prd->price + $order_prd->shippingFee);
            $point_use = $order_prd->pointAmount;
            $pname = addslashes($order_prd->productName);
            $SellingCode = '';  // 아직 미사용
            $pay_type = $talkpay->parsePaymentMethodType($order_prd->paymentMethodType);
            $deliveryMessage = addslashes($order_prd->deliveryMessage);

            // 재고 코드
            $option_idx = '';
            if ($order_prd->productType == 'PRODUCT') { // 옵션 없는 상품
                $complex_no = $pdo->row("select complex_no from erp_complex_option where pno='$pno' and opts='' and del_yn='N'");
            } else {
                $complex_no = preg_replace('/x.*$/', '', $order_prd->optionCode);
            }
            foreach ($order_prd->selectItems as $val) {
                if ($option_idx) $option_idx .= ',';
                $option_idx .= $val->id;
            }

            // 옵션 명 및 옵션 금액
            $option_prc = 0;
            $option = '';
            if ($option_idx) {
                $option_tmp = $pdo->assoc("
                    select
                        sum(i.add_price) as add_price,
                        group_concat(concat(s.name, '<split_small>', i.iname) separator '<split_big>') as name
                    from
                        {$tbl['product_option_set']} s inner join {$tbl['product_option_item']} i on s.no=i.opno
                    where
                        i.no in ($option_idx)
                ");

                $option_prc = $option_tmp['add_price'];
                $option = $option_tmp['name'];
            }

            // 날짜 정보
            $date1 = $talkpay->parseDateFormat($order_prd->orderDateTime, true);
            $date2 = $talkpay->parseDateFormat($order_prd->paidDateTime, true);
            $date3 = $talkpay->parseDateFormat($order_prd->preparingDeliveryDateTime, true);
            $date4 = $talkpay->parseDateFormat($order_prd->inDeliveryDateTime, true);
            $date5 = $talkpay->parseDateFormat($order_prd->deliveryCompleteDateTime, true);
            $date6 = $talkpay->parseDateFormat($order_prd->purchaseDecisionDateTime, true);
            $repay_date = $talkpay->parseDateFormat($order_prd->canceledDateTime, true);

            // 배송 정보
            $current_dlv_prc = $order_prd->shippingFee;
            if($order_prd->shippingFeePayType == 'POSTPAID') {
                $current_dlv_prc = 0;
            }

            // 택배 정보
            $dlv_no = $talkpay->matchLogistics($order_prd->logisticsCode, $partner_no);
            $dlv_code = $order_prd->invoiceNo;

            // 주문자 정보
            $buyer_name = addslashes($order_prd->purchaserNickname);
            $buyer_cell = $talkpay->parsePhoneFormat($order_prd->purchaserPhoneNumber);

            // 수령자 정보
            $addressee_name = $order_prd->receiverName;
            $addressee_zip = $order_prd->zipCode;
            $addressee_addr1 = addslashes($order_prd->baseAddress);
            $addressee_addr2 = addslashes($order_prd->detailAddress);
            $addressee_cell = $order_prd->receiverMobileNumber;

            // 광고 유입 코드
            $conversion = $order_prd->salesCode;

            // 커스텀데이터 1
            $mobile = 'N'; // 모바일 주문 여부
            if ($order_prd->customData1) {
                $customData1 = json_decode($order_prd->customData1);
                if ($customData1->browser_type == 'mobile') {
                    $mobile = 'Y';
                }
            }

            // 주문상품 저장
            if ($oprd['no'] > 0) {
                // 재고 차감
                $err = orderStock($order_no, $oprd['stat'], $stat, $oprd['no']);
                if($err == 20 && $stat <= 2) $stat = 20;

                $pdo->query("
                    update {$tbl['order_product']} set
                        stat='$stat', dlv_code='$dlv_code', dlv_no='$dlv_no',
                        r_addr1='$addressee_addr1', r_addr2='$addressee_addr2', r_zip='$addressee_zip', r_name='$addressee_name',
                        r_phone='$addressee_phone', r_cell='$addressee_cell',
                        external_last_chg='$modifiedDateTime'
                    where external_id='$cono'
                ");

                if ($pdo->lastRowCount() > 0) {
                    $total_update++;
                }

                // 주문서 수정 (주소가 수정될수 있음)
                $pdo->query("
                    update {$tbl['order']} set
                        date1='$date1', date2='$date2', date3='$date3', date4='$date4', date5='$date5', repay_date='$repay_date',
                        addressee_addr1='$addressee_addr1', addressee_addr2='$addressee_addr2',
                        addressee_zip='$addressee_zip', addressee_name='$addressee_name'
                    where ono='$order_no'
                ");

                // 자동 보류
                $GLOBALS['prevent_resolve'] = false;
                if($oprd['dlv_hold'] == 'Y') {
                    $GLOBALS['prevent_resolve'] = true;
                }

            } else {
                // 입점몰 배송비 정산
                $dlv_partner_no = ($prd['dlv_type'] == 1) ? '0' : $prd['partner_no'];
                $partner_no = $prd['partner_no'];
                if($cfg['use_partner_delivery'] == 'Y') { // 업체별 배송 사용시
                    if(!$pdo->row("select count(*) from {$tbl['order_dlv_prc']} where ono='$order_no' and partner_no='$partner_no'")) {
                        $pdo->query("
                            insert into {$tbl['order_dlv_prc']}
                            (ono, partner_no, dlv_prc, first_prc)
                            values ('$order_no', '$partner_no', '$current_dlv_prc', '$current_dlv_prc')
                        ");
                    }
                }

                // 입점몰 정산
                if($cfg['use_partner_shop'] == 'Y') {
                    $fee_prc = getPercentage($total_prc, $prd['partner_rate']);
                    $pasql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
                    $pasql2 .= ", '$partner_no', '{$prd['partner_rate']}', '$fee_prc', '{$prd['dlv_type']}'";
                }

                $pdo->query(trim("
                    INSERT INTO {$tbl['order_product']} (pno, ono, name, sell_prc, buy_ea, total_prc, `option`, option_prc, complex_no, option_idx, stat, dlv_code, dlv_hold, dlv_no, external_id, external_last_chg $pasql1)
                    SELECT '$pno', '$order_no', '$pname', '$sell_prc', '$buy_ea', '$price', '$option', '$option_prc', '$complex_no', '$option_idx', '$stat', '$dlv_code', 'N', '$dlv_no', '$cono', '$modifiedDateTime' $pasql2
                    FROM dual
                    WHERE NOT EXISTS (SELECT * FROM {$tbl['order_product']} WHERE external_id='$cono')
                "));
                $oprd['no'] = $pdo->lastInsertid();

                // 재고 차감
                $err = orderStock($order_no, 0.99, $stat, $oprd['no']);
                if($err == 20 && $stat <= 2) {
                    $pdo->query("update {$tbl['order_product']} set stat=20 where exteranal_id='$cono'");
                }

                // 주문서 생성
                $pdo->query("
                    insert into {$tbl['order']}
                    (ono, date1, date2, date4, date5, buyer_name, buyer_phone, buyer_cell, pay_type, dlv_prc, addressee_addr1, addressee_addr2, addressee_zip, addressee_name, addressee_phone, addressee_cell, dlv_no, dlv_code, dlv_memo, mobile, conversion, external_order)
                    values
                    ('$order_no', '$date1', '$date2', '$date4', '$date5', '$buyer_name', '$buyer_phone', '$buyer_cell', '$pay_type', '$current_dlv_prc', '$addressee_addr1', '$addressee_addr2', '$addressee_zip', '$addressee_name', '$addressee_phone', '$addressee_cell', '$dlv_no', '$dlv_code', '$deliveryMessage', '$mobile', '$conversion', 'talkpay')
                ");

                $total_insert++;
            }
        }

    }

    // 다음 페이지 있을 경우 반복
    $continuationToken = $res->continuationToken;
    if ($res->continuationToken == null) {
        break;
    }
}

// 수집된 주문 기준으로 주문서 처리
foreach ($orders as $ono) {
    // 주문 상품
    $oprd = $pdo->assoc("
        select sum(total_prc) as total_prc,
        sum(if(stat in(1,2,3,4,5,12,14,16), total_prc, 0)) as prd_prc
        from {$tbl['order_product']} where ono='$ono'
     ");

     // 주문서
    $ord = $pdo->assoc("select stat, dlv_prc from {$tbl['order']} where ono='$ono'");
    $dlv_prc = $ord['dlv_prc'];

    // 업체별 배송비 총합
    if($cfg['use_partner_delivery'] == 'Y') { // 업체별 배송 사용시
        $__dlv_prc = $pdo->row("select sum(dlv_prc) from {$tbl['order_dlv_prc']} where ono='$ono'");
        if ($__dlv_prc > 0) {
            $dlv_prc = $__dlv_prc;
        }
    }

    $total_prc = $oprd['total_prc']+$dlv_prc;
    $prd_prc = $oprd['prd_prc'];
    $pay_prc = $oprd['prd_prc']+$dlv_prc;
    $repay_prc = $oprd['total_prc']-$oprd['prd_prc'];
    if ($prd_prc == 0) { //전체 환불일 경우 배송비 결제 금액 0원처리
        $dlv_prc = 0;
        $repay_prc += $dlv_prc;
    }

    // 주문서 업데이트
    $pdo->query("
        update {$tbl['order']} set
            prd_prc='$prd_prc', pay_prc='$pay_prc', total_prc='$total_prc', dlv_prc='$dlv_prc', repay_prc='$repay_prc'
            where ono='$ono'
    ");
    $stat = ordChgPart($ono, false);
    if($stat != $ord['stat']) {
        ordStatLogw($ono, $stat, 'Y');
    }

    // 배송보류 처리
    ordChgHold($ono);

    // ERP 전송
    if(is_object($erpListener)) {
        $erpListener->setOrder($ono);
    }
}

header('Content-type:application/json');
exit(json_encode(array(
    'result' => true,
    'order' => array(
        'start_date' => $dates,
        'finish_date' => $datee,
        'fetch' => $total_fetch,
        'insert' => $total_insert,
        'update' => $total_update
    )
)));