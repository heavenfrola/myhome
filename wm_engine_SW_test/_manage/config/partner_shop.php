<?PHP

	if($cfg['use_partner_shop'] != 'Y') $cfg['use_partner_shop'] = 'N';
	if($cfg['use_partner_delivery'] != 'Y') $cfg['use_partner_delivery'] = 'N';
	if($cfg['partner_prd_accept'] != 'Y') $cfg['partner_prd_accept'] = 'N';
	if(!$cfg['partner_sms_config']) $cfg['partner_sms_config'] = '3';
	if(!$cfg['partner_prd_ref']) $cfg['partner_prd_ref'] = 'N';
	if(!$cfg['partner_account_date']) $cfg['partner_account_date'] = '2';
    $scfg->def('use_partner_npayr', 'N');

	$wec = new WeagleEyeClient($_we, 'account');
	$partnershop_ea = $wec->call('getPartnershopInfo');
    if ($partnershop_ea != 'unlimited') {
        $partnershop_ea = (int) $partnershop_ea;
    }

	if(!isTable($tbl['partner_sms'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['partner_sms']);
	}

	$_partner_account_date = array(
		2 => $_order_stat[2].'일',
		4 => $_order_stat[4].'일',
		5 => $_order_stat[5].'일',
	);

	addField($tbl['partner_shop'], 'partner_sms_use', "enum('N', 'Y') not null default 'N'");
	addField($tbl['partner_shop'], 'partner_sms', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_email', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_email_use', "enum('Y', 'N') not null default 'N'");

?>
<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@partner_shop.exe">
	<input type="hidden" name="config_code" value="partner_shop">
	<table class="tbl_row">
		<caption>입점몰 설정</caption>
		<colgroup>
			<col width="150px">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">입점몰 사용</th>
				<td>
					<label><input type="radio" name="use_partner_shop" value="N" <?=checked($cfg['use_partner_shop'], 'N')?>> 사용안함</label>
					<label><input type="radio" name="use_partner_shop" value="Y" <?=checked($cfg['use_partner_shop'], 'Y')?>> 사용함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">배송처리</th>
				<td>
					<label><input type="radio" name="use_partner_delivery" value="N" <?=checked($cfg['use_partner_delivery'], 'N')?>> 본사 배송</label>
					<label><input type="radio" name="use_partner_delivery" value="Y" <?=checked($cfg['use_partner_delivery'], 'Y')?>> 입점사 별 배송</label>
				</td>
			</tr>
			<tr>
				<th scope="row">네이버페이 반품 주소</th>
				<td>
					<label><input type="radio" name="use_partner_npayr" value="N" <?=checked($cfg['use_partner_npayr'], 'N')?>> 본사 주소</label>
					<label><input type="radio" name="use_partner_npayr" value="Y" <?=checked($cfg['use_partner_npayr'], 'Y')?>> 입점사 주소</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상품등록</th>
				<td>
					<label><input type="radio" name="partner_prd_accept" value="Y" <?=checked($cfg['partner_prd_accept'], 'Y')?>> 본사 승인</label>
					<label><input type="radio" name="partner_prd_accept" value="N" <?=checked($cfg['partner_prd_accept'], 'N')?>> 입점사 직접수정</label>
				</td>
			</tr>
			<tr>
				<th scope="row">관련상품</th>
				<td>
					<label><input type="radio" name="partner_prd_ref" value="N" <?=checked($cfg['partner_prd_ref'], 'N')?>> 본사 등록</label>
					<label><input type="radio" name="partner_prd_ref" value="Y" <?=checked($cfg['partner_prd_ref'], 'Y')?>> 입점사 직접등록</label>
				</td>
			</tr>
			<tr>
                <th scope="row">이메일 및 문자알림</th>
				<td>
					<label><input type="radio" name="partner_sms_config" value="1" <?=checked($cfg['partner_sms_config'], '1')?>> 전체 설정</label>
					<label><input type="radio" name="partner_sms_config" value="2" <?=checked($cfg['partner_sms_config'], '2')?>> 개별 설정</label>
					<label><input type="radio" name="partner_sms_config" value="3" <?=checked($cfg['partner_sms_config'], '3')?>> 설정안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">정산 검색 기준</th>
				<td>
					<?=selectArray($_partner_account_date, 'partner_account_date', null, false, $cfg['partner_account_date'])?>
				</td>
			</tr>
			<?php if ($partnershop_ea != 'unlimited') { ?>
			<tr>
				<th scope="row">등록가능 입점사수</th>
				<td>
					<?=number_format($partnershop_ea)?> 개
					<span class="box_btn_s"><input type="button" value="추가" onclick="goMywisa('?body=support@service@order&service=partnershop&type=4');"></span>
					<i class="icon_info"></i> 2개 이상의 입점사 등록은 결제가 필요합니다.
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>