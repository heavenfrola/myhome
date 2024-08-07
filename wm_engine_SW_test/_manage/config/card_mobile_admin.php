<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  모바일 카드연동 설정
	' +----------------------------------------------------------------------------------------------+*/

	$mobile_testmode = $cfg['card_mobile_test'] == 'Y' ? '테스트' : '실결제';

	#Dacom
	if($cfg['card_mobile_pg'] == 'dacom') {
		$testmode = $cfg['card_mobile_test'] == 'Y' ? '테스트' : '실결제';
		if(!$cfg['card_mobile_dacom_id'] || !$cfg['card_mobile_dacom_key']) $cfg['card_mobile_pg'] = '';
		if(!$cfg['dacom_part_cancel']) $cfg['dacom_part_cancel'] = 'N';
	}

	#KCP
	if($cfg['card_moible_pg'] == 'kcp') {
		$testmode = $cfg['card_moible_test'] == '_test' ? '테스트' : '실결제';
		if(!$cfg['card_moible_site_cd'] || !$cfg['card_moible_site_key']) $cfg['card_moible_pg'] = '';
	}

    $scfg->def('eximbay_real_stat1m', 'TEST');

	switch ($cfg['card_mobile_pg']) {
		case 'dacom'	: $mpgname = '토스페이먼츠'; break;
		case 'inicis'	: $mpgname = 'KG 이니시스'; break;
		case 'kcp'		: $mpgname = 'NHN KCP'; break;
		case 'allat'	: $mpgname = 'KG 올앳'; break;
		case 'allthegate'	: $mpgname = '올더게이트'; break;
		case 'kspay'	: $mpgname = 'KS 페이'; break;
		case 'nicepay'	: $mpgname = 'NICE PAY'; break;
		default			: $mpgname = '현재 카드연동설정이 되어 있지 않습니다';
	}

?>
<div class="box_title">
	<h2 class="title">모바일 국내결제 PG 설정</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">모바일 국내결제 PG 설정</caption>
	<colgroup>
		<col style="width:15%">
		<col>
	</colgroup>
	<tbody>
		<tr>
			<th><?=$cfg['mobile_name']?>PG</th>
			<td>
				<?=$mpgname?>
				<?php if (!$cfg['card_mobile_pg']) { ?>
				&nbsp;<span class="box_btn_s gray"><input type='button' class='btn3' value='<?=$cfg['mobile_name']?>PG 계약안내/신청' onclick="goMywisa('?body=cooperate@payment');"></span>
				<?php } ?>
			</td>
		</tr>
	</tbody>
</table>
<br>
<!-- 토스페이먼츠 -->
<?php if ($cfg['card_mobile_pg'] == 'dacom') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="dacom">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">토스페이먼츠 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_mobile_test" value="Y" <?=checked($cfg['card_mobile_test'],"Y")?>> 테스트</label>
					<label class="p_cursor"><input type="radio" name="card_mobile_test" value="N" <?=checked($cfg['card_mobile_test'],"N")?>> 실결제</label>
					<?php if (getIsCardTest('mobile')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">카드결제 사용여부</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_mobile_use" value="Y" <?=checked($cfg['card_mobile_use'],"Y")?> <?=checked($cfg['card_mobile_use'],"")?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="card_mobile_use" value="N" <?=checked($cfg['card_mobile_use'],"N")?>> 사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_mobile_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
							<option value="<?=$ii?>" <?=checked($ii,$cfg['card_mobile_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">연동 방식</th>
				<td>
					<label class="p_cursor"><input type="radio" name="pg_mobile_version" value="smartXpaySubmit" checked <?=checked($cfg['pg_mobile_version'],"smartXpaySubmit")?>> smartXpaySubmit</label>
				</td>
			</tr>
			<tr>
				<th scope="row">출력방식</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_mobile_window" value="" <?=checked($cfg['card_mobile_window'],"")?> <?=checked($cfg['card_mobile_window'],"")?>> 새창</label>
					<label class="p_cursor"><input type="radio" name="card_mobile_window" value="iframe" <?=checked($cfg['card_mobile_window'],"iframe")?>> 프레임</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_mobile_dacom_id']?></td>
			</tr>
			<tr>
				<th scope="row">상점 키값(Mert Key)</th>
				<td><?=$cfg['card_mobile_dacom_key']?></td>
			</tr>
			<tr>
				<th scope="row">페이나우</th>
				<td>
					<!-- <label><input type="radio" name="sxpay_use_paynow" value="N" <?=checked($cfg['sxpay_use_paynow'], 'N')?>> PG 결제창 내에서 선택</label> -->
					<label><input type="checkbox" name="sxpay_use_paynow" value="Y" <?=checked($cfg['sxpay_use_paynow'], 'Y')?>> 결제 선택시 Paynow 추가</label>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- KCP -->
<?php if ($cfg['card_mobile_pg'] == 'kcp') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="kcp">
	<input type="hidden" name="config_code" value="card_pg">
	<input type="hidden" name="pg_mobile_version" value="smartPay">
	<table class="tbl_row">
		<caption class="hidden">KCP 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_mobile_test" value="_test" <?=checked($cfg['card_mobile_test'],"_test")?>> 테스트</label>
					<label class="p_cursor"><input type="radio" name="card_mobile_test" value="" <?=checked($cfg['card_mobile_test'],"")?>> 실결제</label>
					<?php if (getIsCardTest('mobile')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_mobile_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
						<option value="<?=$ii?>" <?=checked($ii,$cfg['card_mobile_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">사이트 코드</th>
				<td><?=$cfg['card_mobile_site_cd']?></td>
			</tr>
			<tr>
				<th scope="row">사이트 키</th>
				<td><?=$cfg['card_mobile_site_key']?></td>
			</tr>
			<tr>
				<th scope="row">사이트 명</th>
				<td><?=$cfg['card_mobile_site_name']?></td>
			</tr>
			<tr>
				<th scope="row">신용카드<br>부분취소 사용</th>
				<td>
					<label class="p_cursor"><input type="radio" name="kcp_mobile_part_cancel" value="Y" <?=checked($cfg['kcp_mobile_part_cancel'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="kcp_mobile_part_cancel" value="N" <?=checked($cfg['kcp_mobile_part_cancel'], 'N').checked($cfg['kcp_mobile_part_cancel'], '')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>신용카드 부분취소 사용 설정 시 반드시 KCP와 부분취소 계약 여부를 확인하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>
				가상계좌에 고객님이 입금하셨을 경우 자동으로 입금확인 상태가 되게 하려면,<br>
				홈 &gt; 상점정보관리 &gt; 정보변경 &gt; 공통URL정보 <b>[공통URL 변경후] </b> 입력란에 다음과 같이 입력해주세요.<br>
				<strong><?=$root_url?>/main/exec.php?exec_file=vbank/return.php</strong><br>
			</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- KG 이니시스 -->
<?php if ($cfg['card_mobile_pg'] == 'inicis') { ?>
<form name="cardPGFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="inicis">
	<input type="hidden" name="pg_mobile_version" value="INIpayMobileWeb">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">KG 이니시스 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_inicis_mobile_id']?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- 올더게이트 -->
<?php if ($cfg['card_mobile_pg'] == 'allthegate') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="allthegate">
	<input type="hidden" name="pg_mobile_version" value="mobile">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">allthegate 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_mobile_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
							<option value="<?=$ii?>" <?=checked($ii,$cfg['card_mobile_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['mobile_allthegate_StoreId']?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- allat -->
<?php if ($cfg['card_mobile_pg'] == 'allat') { ?>
<form name="cardPGFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="allat">
	<input type="hidden" name="pg_mobile_version" value="mobile">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">allat 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">파트너아이디</th>
				<td><?=$cfg['mobile_card_partner_id']?></td>
			</tr>
			<tr>
				<th scope="row">Cross Key</th>
				<td><?=$cfg['mobile_card_cross_key']?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- KS 페이 -->
<?php if ($cfg['card_mobile_pg'] == 'kspay') { ?>
<form name="cardPGFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="kspay">
	<input type="hidden" name="pg_mobile_version" value="mobile">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">KS 페이 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['kspay_m_storeid']?></td>
			</tr>
			<tr>
				<th scope="row">최대 할부기간</th>
				<td><?=$cfg['kspay_m_installrange']?> 개월</td>
			</tr>
		</body>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- NICE PAY -->
<?php if ($cfg['card_mobile_pg'] == 'nicepay') { ?>
<form name="cardPGFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="nicepay">
	<input type="hidden" name="pg_mobile_version" value="mobile">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">NICE PAY 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">상점아이디</th>
				<td><?=$cfg['nicepay_m_mid']?></td>
			</tr>
			<tr>
				<th scope="row">상점키</th>
				<td><?=$cfg['nicepay_m_licenseKey']?></td>
			</tr>
			<tr>
				<th scope="row">거래취소비밀번호</th>
				<td><input type="password" name="nicepay_m_pwd" value="<?=inputText($cfg['nicepay_m_pwd'])?>" class="input" maxlength="10"></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>

<?php if ($cfg['card_mobile_pg'] == 'eximbay') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_mobile_pg" value="eximbay">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">토스페이먼츠 모바일 PG연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
            <tr>
                <th scope="row">실행모드</th>
                <td>
                    <label><input type="radio" value="TEST" name="eximbay_real_stat1" <?=checked($cfg['eximbay_real_stat1'],'TEST')?>> 테스트</label>
                    <label><input type="radio" value="LIVE" name="eximbay_real_stat1" <?=checked($cfg['eximbay_real_stat1'],'LIVE')?>> 실결제</label>
                </td>
            </tr>
			<tr>
				<th scope="row">가맹점 아이디</th>
				<td><?=$cfg['eximbay_mall_id1m']?></td>
			</tr>
			<tr>
				<th scope="row">가맹점 Secret Key</th>
				<td><?=$cfg['eximbay_secret_key1m']?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>