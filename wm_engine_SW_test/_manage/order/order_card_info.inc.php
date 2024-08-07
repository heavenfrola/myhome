<?php

/**
 * 주문서 상세 카드 정보
 **/

if (!$card['no']) return;
if ($data['pay_type'] == 4) return;
$pg_link = linkPGAdmin($card['pg']);

// 할부개월 표시 (간편결제인 경우 PG정보 표시)
if ($data['pay_type'] == '1' || $data['pay_type'] == '25' || $data['pay_type'] == '27') {
    $card['quota_str'] = ($card['quota'] > 0) ? $card['quota'].'개월' : '일시불';
} elseif($data['pay_type'] == '7') {
    $card['quota_str'] = '휴대폰';
} elseif($data['pay_type'] == '10') {
    $card['quota_str'] = 'Alipay';
} elseif($data['pay_type'] == '12') {
    $card['quota_str'] = 'kakaopay';
} elseif($data['pay_type'] == '13') {
    $card['quota_str'] = 'Paypal';
} elseif($data['pay_type'] == '14') {
    $card['quota_str'] = 'Cyrexpay';
} elseif($data['pay_type'] == '15') {
    $card['quota_str'] = "EContext";
} elseif($data['pay_type'] == '16') {
    $card['quota_str'] = 'Paypal';
} elseif($data['pay_type'] == '20') {
    $card['quota_str'] = "Eximbay";
} elseif($data['pay_type'] == '17') {
    $card['quota_str'] = 'payco '.$card['use_pay_method'];
    if($data['bank']) $card['card_name'] = $data['bank'];
} elseif($data['pay_type'] == '18') {
    $card['quota_str'] = 'Wechat';
} elseif($data['pay_type'] == '19') {
    $card['quota_str'] = 'Alipay';
} elseif($data['pay_type'] == '22') {
    $card['quota_str'] = 'TossPay';
} elseif($data['pay_type'] == '21') {
    $card['quota_str'] = 'PAYNOW';
    if($data['bank']) $card['card_name'] = $data['bank'];
} elseif($data['pay_type'] == '28') {
    $card['quota_str'] = 'samsungpay';
} else {
    $card['quota_str'] = '계좌이체';
}

// PG별 부분 취소 가능 여부
$card_cancel_plug = 'ALL';
if($card['pg'] == 'dacom') $card_cancel_plug='';
if($card['pg'] == 'kcp' && (($data['mobile'] == 'Y' && $cfg['kcp_mobile_part_cancel'] == 'Y') || ($data['mobile'] != 'Y' && $cfg['kcp_part_cancel'] == 'Y'))) $card_cancel_plug='';
if($card['pg'] == 'inicis' && $cfg['pg_version'] != 'INILite') $card_cancel_plug='';
if($card['pg'] == 'allthegate') $card_cancel_plug='';
if($card['pg'] == 'kspay') $card_cancel_plug='';
if($card['pg'] == 'allat') $card_cancel_plug='';
if($card['pg'] == 'alipay') $card_cancel_plug='';
if($card['pg'] == 'paypal') $card_cancel_plug='';
if($card['pg'] == 'eximbay') $card_cancel_plug = ($data['pay_type'] == '1') ? 'tax' : '';
if($card['pg'] == 'kakaopay') $card_cancel_plug='';
if($card['pg'] == 'payco') $card_cancel_plug='';
if($card['pg'] == 'wechat') $card_cancel_plug='';
if($card['pg'] == 'alipay_e') $card_cancel_plug='';
if($card['pg'] == 'tosspay') $card_cancel_plug='';
if($card['pg'] == 'nicepay') $card_cancel_plug='';
if($card['pg'] == 'nsp') $card_cancel_plug = ($cfg['nsp_use_tax'] == 'Y') ? 'tax' : '';
if($card['pg'] == 'samsungpay') $card_cancel_plug = '';

// 카드 취소 로그
$card_log_sql = $pdo->iterator("select * from {$tbl['card_cc_log']} where cno='{$card['no']}' and ono='$ono'");

// pg사 링크
if($card['pg'] == 'danal' || $card['pg'] == 'samsungpay') $pg_link = 'https://partner.danalpay.com/';
else if($card['pg']=='kakaopay') $pg_link = 'https://pg-web.kakao.com/v1/confirmation/p/';
else if ($card['pg'] == 'nsp') $pg_link = 'https://admin.pay.naver.com/';


// 영수증 링크
switch($card['pg']) {
    case 'kcp' :
        $rcpt_link = "http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=$card[tno]";
        break;
    case 'inicis' :
        $rcpt_link = "https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid=$card[tno]&noMethod=1";
        break;
    case 'dacom' :
        $card_dacom_id = ($data['mobile'] == 'Y') ? $cfg['card_mobile_dacom_id'] : $cfg['card_dacom_id'];
        $card_dacom_key = ($data['mobile'] == 'Y') ? $cfg['card_mobile_dacom_key'] : $cfg['card_dacom_key'];
        $card['authdata'] = md5($card_dacom_id.$card['tno'].$card_dacom_key);
        $rcpt_link = "javascript:showReceiptByTID('$card_dacom_id', '$card[tno]', '$card[authdata]')";
        break;
    case 'allthegate';
        $send_dt = substr($card['app_time'], 0, 8);
        $rcpt_link = "http://allthegate.com/customer/receiptLast3.jsp?sRetailer_id=$cfg[allthegate_StoreId]&approve=$card[app_no]&deal_no=$card[tno]&send_dt=$send_dt";
        break;
    case 'allat' :
        $allat_id = $data['mobile'] == 'Y' ? $cfg['mobile_card_partner_id'] : $cfg['card_partner_id'];
        $bank_pay_method = $data['pay_type'] == 5 ?'ABANK' : 'VBANK';

        if($data['pay_type'] == 4 || $data['pay_type'] == 5) {
            $rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_tx_receipt.jsp?shop_id=$allat_id&order_no=$ono&pay_type=$bank_pay_method";
        } else if($data['pay_type'] == 7) {
            $rcpt_link = '';
        } else {
            $rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_card_receipt.jsp?shop_id=$allat_id&order_no=$ono";
        }
        break;
    case 'kspay' :
        $rcpt_link = "javascript:ksnetReceipt('$card[tno]');";
        break;
    case 'kakaopay' :
        $total_hash = $cfg['kakao_cid'].$card['tno'].$data['ono'].($data['member_no'] > 0 ? $data['member_id'] : $card['guest_no']);
        $total_hash = hash('sha256', $total_hash, true);
        $kakao_hash = bin2hex($total_hash);
        $rcpt_link = "javascript:kakaoReceipt('$card[tno]', '$kakao_hash', '$data[mobile]');";
        break;
    case 'payco' :
        $rcpt_link = "javascript:paycoReceipt('$data[ono]', '$card[ordr_idxx]', '{$cfg['payco_sellerKey']}');";
        break;
    case 'tosspay':
        $rcpt_link = "javascript:tossReceipt('{$card['tno']}');";
        break;
    case 'nicepay' :
        $rcpt_link = 'javascript:nicepayReceipt(\''.$card['tno'].'\');';
        break;
    case 'samsungpay' :
        $rcpt_link = "javascript:samsungpayReceipt('{$card['tno']}');";
        break;
    default :
        $rcpt_link = '';
}
$rcpt_target = ($card['pg'] == 'dacom' || $card['pg'] == 'kspay' || $card['pg'] == 'kakaopay' || $card['pg'] == 'payco' || $card['pg'] == 'nicepay' || $card['pg'] == 'tosspay' || $card['pg'] == 'samsungpay') ? '' : 'target="_blank"';

?>
<tr class="card_info">
    <th scope="row">카드 정보</th>
    <td>
        <?php if($card['stat'] == "2") { ?>
        <p class="pgcancel">
            <?php if($admin['level'] > 2 && strchr($admin['auth'], '@cardcc') == false) { ?>
            카드 취소 권한이 없습니다.
            <?php } else { ?>
            'PG사 결제 환불'버튼을 통해 환불처리가 가능합니다.
            <span class="box_btn_s gray"><input type="button" value="PG사 결제 환불" onclick="cardCancel('<?=$card_cancel_plug?>');"></span>
            <?php } ?>
        </p>
        <?php } ?>
        <div class="card_cancel_div card_cancel_box" style="display:none;">
            <?php if ($card_cancel_plug == 'tax') { ?>
            <!-- 복합과세 취소 -->
            과세금액 <input type="text" name="taxScopeAmount" size="10" class="input block" value="<?=parsePrice($card['wm_price']-$card['wm_free_price'])?>" placeholder="과세금액">
            비과세 금액 <input type="text" name="taxExScopeAmount" size="10" class="input block" value="<?=parsePrice($card['wm_free_price'])?>" placeholder="비과세금액">
            <span class="box_btn_s full white"><input type="button" value="확인" onClick="cancelTax('<?=$card['no']?>', this);"></span>
            <?php } else { ?>
            <!-- 일반과세 취소 -->
            <label class="p_cursor"><input type="radio" name="card_cancel_type" value="1" onclick="if(this.checked) this.form.card_cancel_price.value='<?=parsePrice($card['wm_price'])?>'; if(this.checked) this.form.card_cancel_price.disabled=true;" checked> 전체환불</label>
            <label class="p_cursor"><input type="radio" name="card_cancel_type" value="2" onclick="if(this.checked) this.form.card_cancel_price.disabled=false;"> 부분환불</label>
            <input type="text" name="card_cancel_price" size="10" class="input block" value="<?=parsePrice($card['wm_price'])?>" disabled>
            <span class="box_btn_s full white"><input type="button" value="확인" onClick="preventClick(this); window.frames[hid_frame].location.href='./?body=order@order_card_cancel.exe&cno=<?=$card['no']?>&price='+this.form.card_cancel_price.value+'&card_cancel_type='+$(':checked[name=card_cancel_type]').val();"></span>
            <?php } ?>

            <?php if($card['pg'] == 'inicis') { ?>
            <!-- 이니시스 부분취소 안내 -->
            <ul class="list_info left">
                <li>이니시스 부분환불은 <strong>국민, BC, 삼성, 외환, 신한, 현대, 롯데</strong>의 7개 카드사만 지원됩니다.(PG사 사정에 따라 변경될 수 있습니다.)</li>
                <li>계열사 카드는 부분환불이 불가능합니다.(시티은행에서 발행된 신한카드 등)</li>
                <li>BC카드는 원거래금액의 90%까지만 부분환불이 가능합니다.</li>
                <li>현대, 롯데카드를 제외하고 최대 9회까지만 부분환불 가능합니다.(부분환불 회수가 초과된 결제건의 경우 전체환불도 불가능합니다.)</li>
                <li>할부거래를 부분환불하실 때 잔액이 5만원 이하이면 일시불로 변경됩니다.</li>
            </ul>
            <?php }?>

            <?php if($card['pg'] == 'kspay') { ?>
            <!-- ksnet 부분취소 안내 -->
            <ul class="list_info left">
                <li>1회 부분환불 후 추가 부분환불 및 잔여금액 전체환불은 ksnet 관리자에서만 가능합니다.</li>
                <li>당일 결제건의 부분환불 시 오류가 발생할 수 있습니다. 문제 발생 시 ksnet에 문의해주시기 바랍니다.</li>
            </ul>
            <?php } ?>
        </div>

        <?php if($card['stat'] == "3"){ ?>
        <p class="pgcancel">
            <span class="p_color2">환불처리가 완료된 주문서입니다.</span>
        </p>
        <?php } ?>

        <!-- 카드 취소 로그 -->
        <?php if ($card_log_sql->rowCount() > 0) { ?>
        <ul class="log">
            <?php foreach ($card_log_sql as $card_log_data) { ?>
            <li>
                <span><?=date("Y-m-d H:i", $card_log_data['reg_date'])?></span> : 관리자[<?=$card_log_data['admin_id']?>]님이 요청한
                <?php if ($card_log_data['price'] > 0) { ?>
                <span><?=parsePrice($card_log_data['price'], true)?></span> <?=$cfg['currency_type']?>
                <?php } ?>
                <?php if ($card_log_data['stat'] == '2') { ?>
                <div><strong>환불 완료</strong> (<?=$card_log_data['res_msg']?>)</div>
                <?php } else { ?>
                <div><strong>환불 실패</strong> (<?=$card_log_data['res_msg']?>)</div>
                <?php } ?>
                </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </td>
    <td class="lb vtat">
        <p class="pg_info">
            카드 정보 : <span class="val"><?=$card['card_name']?> (<?=$card['quota_str']?>) - <?=strip_tags($card['res_msg'])?></span>
            <span class="box_btn_s2 btn"><a href="<?=$pg_link?>" target="_blank">PG사 확인</a></span>
        </p>
        <p class="pg_info">
            거래 번호 : <span class="val"><?=$card['tno']?></span>
            <?php if ($rcpt_link) { ?>
            <!-- 카드 영수증 -->
            <script type="text/javascript" src="//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js"></script>
            <span class="box_btn_s2 btn"><a href="<?=$rcpt_link?>" <?=$rcpt_target?>>영수증 확인</a></span>
            <?php } ?>
        </p>
        <?php if ($card['pg'] == 'samsungpay') { ?>
        <p class="pg_info">
            승인 번호 : <span class="val"><?=$card['app_no']?></span>
        </p>
        <?php } ?>
    </td>
</tr>