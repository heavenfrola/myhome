<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  결제 설정
	' +----------------------------------------------------------------------------------------------+*/

    // 구버전 설정 마이그레이션
    include_once __ENGINE_DIR__.'/_engine/include/migration/cfg_paytype.inc.php';

    // 결제 설정
    $card_pg = getPGName();
    if (empty($card_pg) == false) {
        $card_pg = '('.$card_pg.')';
    }
    $autobill_pg = getSubscriptionPGName();
    if (empty($autobill_pg) == false) {
        $autobill_pg = '('.$autobill_pg.')';
    }

    // 무통장 입금 기한
    $use_bank_time = ($scfg->comp('banking_time') == true) ? 'Y' : 'N';

	// 미입금주문 결제수단 변경
	$changeable_checked = (empty($cfg['change_pay_type']) == false) ? explode('@', $cfg['change_pay_type']) : array();
	$_pay_type_changeable = array(
		1 => $_pay_type[1],
	);
	if (empty($cfg['use_paytype_change']) == true) $cfg['use_paytype_change'] = 'N';
	if (empty($cfg['change_pay_recalc']) == true) $cfg['change_pay_recalc'] = 'Y';

    // 미입금주문 결제수단에 해외결제 수단 추가
    if ($scfg->comp('use_alipay', 'Y') == true) $_pay_type_changeable[10] = $_pay_type[10];
    if ($scfg->comp('use_sbipay', 'Y') == true) $_pay_type_changeable[15] = $_pay_type[15];
    if ($scfg->comp('use_paypal_c', 'Y') == true) $_pay_type_changeable[16] = $_pay_type[16];
    if ($scfg->comp('use_wechat', 'Y') == true) $_pay_type_changeable[18] = $_pay_type[18];
    if ($scfg->comp('use_exim', 'Y') == true) $_pay_type_changeable[20] = $_pay_type[20];

	// 미입금주문 결제수단 변경 스킨 체크
	$_skin = getSkinCfg();
	$pc_skin_name = ($design['edit_skin']) ? $design['edit_skin'] : $design['skin'];
	$pc_skin = $root_dir."/_skin/".$pc_skin_name."/MODULE/mypage_paytype_chg_list.wsm";
	$skin_check = file_exists($pc_skin);

    // 미설정 시 체크 불가
    $disabled_npay = ($scfg->comp('nsp_partnerId') == true) ? '' : 'disabled';
    $disabled_npay_sub = ($scfg->comp('nsp_sub_partnerId') == true) ? '' : 'disabled';
    $disabled_payco = ($scfg->comp('payco_sellerKey') == true) ? '' : 'disabled';
    $disabled_kakaopay = ($scfg->comp('kakao_cid') == true) ? '' : 'disabled';
    $disabled_talkpay = ($scfg->comp('talkpay_ShopKey') == true) ? '' : 'disabled';
    $disabled_tosspay = ($scfg->comp('tossc_liveApiKey') == true) ? '' : 'disabled';
    $disabled_samsungpay = ($scfg->comp('samsungpay_id') == true && $scfg->comp('samsungpay_pwd') == true) ? '' : 'disabled';
    $disabled_paypal = ($scfg->comp('eximbay_mall_id16') == true && $scfg->comp('eximbay_secret_key16') == true) ? '' : 'disabled';
    $disabled_alipay = ($scfg->comp('eximbay_mall_id19') == true && $scfg->comp('eximbay_secret_key19') == true) ? '' : 'disabled';
    $disabled_wechat = ($scfg->comp('eximbay_mall_id18') == true && $scfg->comp('eximbay_secret_key18') == true) ? '' : 'disabled';
    $disabled_sbipay = ($scfg->comp('sbipay_public_key') == true) ? '' : 'disabled';
    $disabled_exim = ($scfg->comp('eximbay_mall_id20') == true && $scfg->comp('eximbay_secret_key20') == true) ? '' : 'disabled';
    $disabled_autobill = ($scfg->get('autobill_pg') != null) ? '' : 'disabled';

?>
<div class="msg_topbar sub quad warning">
	신용카드 및 실시간계좌이체는 서비스 신청 및 설정이 되어있어야 작동합니다. <a href="?body=config@card" class="link">바로가기</a>
</div>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="account">
	<div class="box_title first">
		<h2 class="title">결제 설정</h2>
	</div>
    <table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
        <tbody>
            <tr>
				<th scope="row">국내 결제 <a href="?body=config@card" class="sclink3">설정</a></th>
                <td class="left">
					<ul class="list_common4 black">
                        <li><label><input type="checkbox" name="pay_type_1" value="Y" <?=checked($cfg['pay_type_1'], 'Y')?>> <?=$_pay_type[1]?> <?=$card_pg?></label></li>
                        <li><label><input type="checkbox" name="pay_type_2" value="Y" <?=checked($cfg['pay_type_2'], 'Y')?>> <?=$_pay_type[2]?></label></li>
                        <li><label><input type="checkbox" name="pay_type_4" value="Y" <?=checked($cfg['pay_type_4'], 'Y')?>> <?=$_pay_type[4]?></label></li>
                        <li><label><input type="checkbox" name="pay_type_5" value="Y" <?=checked($cfg['pay_type_5'], 'Y')?>> <?=$_pay_type[5]?></label></li>
                        <li><label><input type="checkbox" name="pay_type_7" value="Y" <?=checked($cfg['pay_type_7'], 'Y')?>> <?=$_pay_type[7]?></label></li>
                    </ul>
                </td>
            </tr>
            <tr>
				<th scope="row">간편 결제 <a href="?body=config@easypay" class="sclink3">설정</a></th>
                <td class="left">
					<ul class="list_common4 black">
                        <li><label><input type="checkbox" disabled <?=checked($scfg->comp('checkout_id'), true)?>> 네이버페이 주문형</label></li>
                        <li><label><input type="checkbox" name="use_nsp" value="Y" <?=checked($cfg['use_nsp'], 'Y')?> <?=$disabled_npay?>> 네이버페이 결제형</label></li>
                        <li><label><input type="checkbox" name="use_payco" value="Y" <?=checked($cfg['use_payco'], 'Y')?> <?=$disabled_payco?>> 페이코</label></li>
                        <li><label><input type="checkbox" name="use_kakaopay" value="Y" <?=checked($cfg['use_kakaopay'], 'Y')?> <?=$disabled_kakaopay?>> 카카오페이</label></li>
                        <li><label><input type="checkbox" name="use_talkpay" value="Y" <?=checked($cfg['use_talkpay'], 'Y')?> <?=$disabled_talkpay?>> 톡체크아웃 (카카오페이구매)</label></li>
                        <li><label><input type="checkbox" name="use_tosscard" value="Y" <?=checked($cfg['use_tosscard'], 'Y')?> <?=$disabled_tosspay?>> 토스결제</label></li>
                        <li><label><input type="checkbox" name="use_samsungpay" value="Y" <?=checked($scfg->get('use_samsungpay'), 'Y')?> <?=$disabled_samsungpay?>> 삼성페이</label></li>
                    </ul>
                </td>
            </tr>
            <tr>
				<th scope="row">정기 결제 <a href="?body=config@autobill" class="sclink3">설정</a></th>
                <td class="left">
					<ul class="list_common4 black">
                        <li><label><input type="checkbox" name="autobill_pg" value="<?=$cfg['autobill_pg']?>" <?=checked($cfg['use_sbscr'], 'Y')?> <?=$disabled_autobill?>> 신용카드 (<?=$autobill_pg?>)</label></li>
                        <li><label><input type="checkbox" name="use_nsp_sbscr" value="Y" <?=checked($cfg['use_nsp_sbscr'] ,'Y')?> <?=$disabled_npay_sub?>> 네이버페이 결제형</label></li>
                    </ul>
                </td>
            </tr>
            <tr>
				<th scope="row">해외 결제 <a href="?body=config@card_int" class="sclink3">설정</a></th>
                <td class="left">
					<ul class="list_common4 black">
                        <?php if ($cfg['use_paypal_direct'] == 'Y') { ?>
                        <li><label><input type="checkbox" name="use_paypal" value="Y" <?=checked($cfg['use_paypal'], 'Y')?>> <?=$_pay_type[13]?></label></li>
                        <?php } else { ?>
                        <li><label><input type="checkbox" name="use_paypal_c" value="Y" <?=checked($cfg['use_paypal_c'], 'Y')?>> <?=$_pay_type[16]?></label></li>
                        <?php } ?>
                        <?php if ($cfg['use_alipay_direct'] == 'Y') { ?>
                        <li><label><input type="checkbox" name="use_alipay" value="Y" <?=checked($cfg['use_alipay'], 'Y')?>> <?=$_pay_type[10]?></label></li>
                        <?php } else { ?>
                        <li><label><input type="checkbox" name="use_alipay_e" value="Y" <?=checked($cfg['use_alipay_e'], 'Y')?>> <?=$_pay_type[19]?></label></li>
                        <?php } ?>
                        <li><label><input type="checkbox" name="use_wechat" value="Y" <?=checked($cfg['use_wechat'], 'Y')?>> <?=$_pay_type[18]?></label></li>
                        <li><label><input type="checkbox" name="use_sbipay" value="Y" <?=checked($cfg['use_sbipay'], 'Y')?>> <?=$_pay_type[15]?></label></li>
                        <li><label><input type="checkbox" name="use_exim" value="Y" <?=checked($cfg['use_exim'], 'Y')?>> <?=$_pay_type[20]?></label></li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="change_pay_type">
	<div class="box_title">
		<h2 class="title">미입금주문 결제수단 변경</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">미입금주문 결제수단 변경</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<label><input type="radio" name="use_paytype_change" value="Y" <?=checked($cfg['use_paytype_change'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="use_paytype_change" value="N" <?=checked($cfg['use_paytype_change'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
                    결제금액 재계산
					<a href="#" class="tooltip_trigger" data-child="tooltip_change_pay_recalc">설명</a>
					<div class="info_tooltip tooltip_change_pay_recalc w700">
						<h3>결제금액 재계산</h3>
                        <ul class="list_info">
                            <li>사용함 설정 시 현재 설정 및 변경된 결제방식 기준으로 할인 및 배송비를 재계산합니다.</li>
                            <li>결제금액을 재계산하지 않을 경우 무통장입금 전영 할인과 종료된 쿠폰 및 할인 등이 변경된 조건과 무관하게 그대로 적용됩니다.</li>
                        </ul>
						<a href="#" class="tooltip_closer">닫기</a>
                    </div>
                </th>
				<td>
					<label><input type="radio" name="change_pay_recalc" value="Y" <?=checked($cfg['change_pay_recalc'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="change_pay_recalc" value="N" <?=checked($cfg['change_pay_recalc'], 'N')?>> 사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">변경 가능 결제수단</th>
				<td>
					<ul class="list_common4 black">
						<?php foreach($_pay_type_changeable as $key => $val) { ?>
						<li>
							<label><input
								type="checkbox"
								name="change_pay_type[]"
								value="<?=$key?>"
								<?=checked(in_array($key, $changeable_checked), true)?>
							> <?=$val?>로 변경</label>
						</li>
						<?php } ?>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>미입금 주문의 결제수단을 변경하여, 마이페이지에서 재결제를 진행할 수 있습니다.</li>
			<li>결제금액 재계산 사용 시, 타임세일의 경우 현재 설정 기준으로 재계산됩니다.</li>
			<li>결제수단 변경은 회원 전용 기능입니다.</li>
			<li><span class="warning">누적 주문수가 많은 쇼핑몰인 경우 최초 설정 시 시간이 다소 소요</span>될 수 있습니다. 혼잡 시간대를 피해 설정해 주세요</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="account_bank_limit">
	<div class="box_title">
		<h2 class="title">주문 입금 기한</h2>
	</div>
    <table class="tbl_row">
        <caption class="hidden">주문 입금 기한</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row" rowspan="2">
                    입금 기한 설정
                    <a href="#" class="tooltip_trigger" data-child="tooltip_bank_time">설명</a>
                    <div class="info_tooltip tooltip_bank_time w700">
                        <h3>자동 취소 문자발송</h3>
                        <p>고객 문자알림 설정에서 미입금 주문 자동 취소 시 자동 문자를 발송할 수 있으며, 발송 제한 시간을 설정할 수 있습니다. <a href="?body=5710" class="link">바로가기</a></p>
                        <a href="#" class="tooltip_closer">닫기</a>
                    </div>
                </th>
                <td>
                    <label><input type="radio" name="use_bank_time" value="Y" <?=checked($use_bank_time, 'Y')?>> 사용함</label>
                    <label><input type="radio" name="use_bank_time" value="N" <?=checked($use_bank_time, 'N')?>> 사용안함</label>
                </td>
            </tr>
            <tr>
                <td>
                     <select name="banking_time_std">
                        <option value="day" <?=checked($cfg['banking_time_std'], 'day', true)?>>주문 날짜</option>
                        <option value="time" <?=checked($cfg['banking_time_std'], 'time', true)?>>주문 시간</option>
                     </select>
                     기준으로
                     <select name="banking_time">
                        <?php for ($ii = 1; $ii <= 30; $ii++) {?>
                        <option value="<?=$ii?>" <?=checked($cfg['banking_time'], $ii, 1)?>><?=$ii?>일</option>
                        <?php } ?>
                    </select>
                    내에 입금하지 않으면 자동으로 주문을 취소합니다.
                </td>
            </tr>
        </tbody>
    </table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
(useBankTime = function() {
    if($(':checked[name=use_bank_time]').val() == 'Y') {
        $('select[name=banking_time_std], select[name=banking_time]').prop('disabled', false);
    } else {
        $('select[name=banking_time_std], select[name=banking_time]').prop('disabled', true);
    }
})();
$('input[name=use_bank_time]').on('change', useBankTime);
</script>

<form name="account_sms" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="account_sms_notice">
	<div class="box_title">
		<h2 class="title">미입금 주문 자동 SMS 통보</h2>
	</div>
	<?php include_once $engine_dir."/_manage/config/account_sms.php"; ?>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php if ($cfg['sale4_able']=="Y") { ?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkSFrm(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="account_cash_sale">
	<div class="box_title">
		<h2 class="title">현금 결제 할인</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">현금 결제 할인</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<td scope="row" colspan="2"><span class="explain">현금 결제 할인이란 고객이 무통장 입금으로 결제할 경우 결제액을 할인해주는 서비스입니다.</span></td>
		</tr>
		<tr>
			<th scope="row">사용여부</th>
			<td><input type="radio" name="sale4_use" id="sale4_use1" value="Y" <?=checked($cfg['sale4_use'],"Y")?>> <label for="sale4_use1" class="p_cursor">사용함</label>  <input type="radio" name="sale4_use" id="sale4_use2" value="N" <?=checked($cfg['sale4_use'],"N").checked($cfg['sale4_use'],"")?>> <label for="sale4_use2" class="p_cursor">사용안함</label></td>
		</tr>
		<tr>
			<th scope="row">할인율</th>
			<td>
				<input type="text" name="sale4_ratio1" value="<?=$cfg['sale4_ratio1']?>" class="input" size="10"> %
				<input type="hidden" name="sale4_ratio2" value="%">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckAccountAdd(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="pg_charge">
	<div class="box_title">
		<h2 class="title">결제방식별 추가금액</h2>
	</div>
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%;">
			<col>
			<col style="width:15%;">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">신용카드</th>
				<td><input type="text" name="pg_charge_1" size="5" maxlength="3" class="input right" value="<?=$cfg['pg_charge_1']?>" onkeyup="FilterNumOnly(this)"> %</td>
				<th scope="row">가상계좌</th>
				<td><input type="text" name="pg_charge_4" size="5" maxlength="3" class="input right" value="<?=$cfg['pg_charge_4']?>" onkeyup="FilterNumOnly(this)"> %</td>
			</tr>
			<tr>
				<th scope="row">계좌이체</th>
				<td><input type="text" name="pg_charge_5" size="5" maxlength="3" class="input right" value="<?=$cfg['pg_charge_5']?>" onkeyup="FilterNumOnly(this)"> %</td>
				<th scope="row">휴대폰결제</th>
				<td><input type="text" name="pg_charge_7" size="5" maxlength="3" class="input right" value="<?=$cfg['pg_charge_7']?>" onkeyup="FilterNumOnly(this)"> %</td>
			</tr>
			<tr>
				<th scope="row">기타 간편결제</th>
				<td colspan="3"><input type="text" name="pg_charge_E" size="5" maxlength="3" class="input right" value="<?=$cfg['pg_charge_E']?>" onkeyup="FilterNumOnly(this)"> %</td>
			</tr>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>상품별 할인 전 상품 금액에 설정된 비율만큼 할증됩니다.</li>
			<li>배송비에는 적용되지 않습니다.</li>
			<li>네이버페이에서는 적용되지 않습니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function checkSFrm(f){
		if (f.sale4_use[0].checked && !checkBlank(f.sale4_ratio1,"할인율을 입력해주세요.")) return false;
		if(!checkNum(f.sale4_ratio1,'할인율은 숫자만 입력해주세요.')) return false;
		if (eval(f.sale4_ratio1.value)<1) {
			window.alert('할인율은 0보다 커야합니다.');
			f.sale4_ratio1.focus();
			return false;
		}

		if (eval(f.sale4_ratio1.value)>100) {
			window.alert('할인율을 %로 설정할 경우 100보다 작아야 합니다.');
			f.sale4_ratio1.focus();
			return false;
		}

		if (!confirm('현재 할인율을 적용하시겠습니까? 설정 즉시 바로 적용됩니다.')) return false;
	}

    $(':checkbox[name=autobill_pg]').on('click keydown', function() {
        window.alert('정기 결제 PG는 직접 해제할 수 없습니다.');
        return false;
    });
</script>