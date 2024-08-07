<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카드연동 설정
	' +----------------------------------------------------------------------------------------------+*/

	$testmode = $cfg['card_test'] == 'Y' ? '테스트' : '실결제';

	#KCP
	if($cfg['card_pg'] == 'kcp') {
		$testmode = $cfg['card_test'] == '_test' ? '테스트' : '실결제';
		if(!$cfg['card_site_cd'] || !$cfg['card_site_key']) $cfg['card_pg'] = '';
	}
	if(!$cfg['kcp_use_taxfree']) $cfg['kcp_use_taxfree'] = 'N';

	#Dacom
	if($cfg['card_pg'] == 'dacom') {
		$pg_version = $cfg['pg_version'] == 'xpayLite' ? 'Xpay2.0 Lite' : '웹전송';
		$testmode = $cfg['card_test'] == 'Y' ? '테스트' : '실결제';
		if(!$cfg['card_dacom_id'] || !$cfg['card_dacom_key']) $cfg['card_pg'] = '';
		if(!$cfg['dacom_part_cancel']) $cfg['dacom_part_cancel'] = 'N';
	}

	#Allat
	if($cfg['card_pg'] == 'allat') {
		if(!$cfg['card_no_interest']) $cfg['card_no_interest']="D";
		if(!$cfg['card_no_interest_mtype']) $cfg['card_no_interest_mtype']=1;
		if(!$cfg['card_partner_id'] || !$cfg['card_form_key']) $cfg['card_cross_key'] = '';
	}

	#inicis
	if($cfg['card_pg'] == 'inicis') {
		if($cfg['pg_version'] == 'INILite') {
			if(!$cfg['card_inicis_id'] || !$cfg['card_inicis_key']) $cfg['card_pg'] = '';
		}
		else if($cfg['pg_version'] == 'INIweb') {
			if(!$cfg['card_web_id']) $cfg['card_pg'] = '';
		}
		else {
			if(!$cfg['card_mall_id'] || !$cfg['card_key_password']) $cfg['card_pg'] = '';
		}
	}

	#alltheGate
	if($cfg['card_pg'] == 'allthegate') {
		if(!$cfg['allthegate_StoreId']) $cfg['card_pg'] = '';
	}

	switch ($cfg['card_pg']) {
		case 'dacom'		: $pgname = '토스페이먼츠'; break;
		case 'inicis'		: $pgname = 'KG 이니시스'; break;
		case 'kcp'			: $pgname = 'NHN KCP'; break;
		case 'allat'		: $pgname = 'KG 올앳'; break;
		case 'allthegate'	: $pgname = '올더게이트'; break;
		case 'kspay'		: $pgname = 'KS 페이'; break;
		case 'nicepay'		: $pgname = 'NICE PAY'; break;
        case 'eximbay'      : $pgname = 'EXIMBAY'; break;
		default				: $pgname = '현재 카드연동설정이 되어 있지 않습니다';
	}

	#NICE PAY
	if(!$cfg['nice_use_taxfree']) $cfg['nice_use_taxfree'] = 'N';

    $scfg->def('eximbay_real_stat1', 'TEST');

?>
<?php if (getIsCardTest()) { ?>
<div class="msg_topbar sub quad warning">
	국내결제 PG 설정 실행모드가 테스트로 되어 있습니다.
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>
<div class="box_title first">
	<h2 class="title">국내결제 PG 설정</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">국내결제 PG 설정</caption>
	<colgroup>
		<col style="width:15%">
		<col>
	</colgroup>
	<body>
		<tr>
			<th scope="row">신용카드PG</th>
			<td>
				<?=$pgname?>
				<?php if (!$cfg['card_pg']){ ?>
				&nbsp;<span class="box_btn_s gray"><input type="button" value="신용카드PG 계약안내/신청" onclick="goMywisa('?body=cooperate@payment');"></span>
				<?php } ?>
			</td>
		</tr>
	</body>
</table>
<br>
<!-- KCP -->
<?php if ($cfg['card_pg'] == 'kcp') { ?>
<div class="box_middle left">
	<ul class="list_info">
		<li>
			가상계좌 주문시 자동 입금확인되려면 아래 경로에서 설정해주시기 바랍니다.<br>
			KCP 상점관리자 > 상점 정보관리 > 정보 변경 > 공통 URL 정보 [공통URL 변경 후] 입력란에 아래 URL을 입력해 주시기 바랍니다.<br>
			<strong><?=$root_url?>/main/exec.php?exec_file=vbank/return.php</strong>
		</li>
	</ul>
</div>
<form name="cardPGFrm1" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="kcp">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<input type="radio" name="card_test" value="_test" <?=checked($cfg['card_test'],"_test")?>> 테스트
					<input type="radio" name="card_test" value="" <?=checked($cfg['card_test'],"")?>> 실결제
					<?php if (getIsCardTest('pc')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
							<option value="<?=$ii?>" <?=checked($ii,$cfg['card_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">사이트 코드</th>
				<td><?=$cfg['card_site_cd']?></td>
			</tr>
			<tr>
				<th scope="row">사이트 키</th>
				<td><?=$cfg['card_site_key']?></td>
			</tr>
			<tr>
				<th scope="row">사이트 명</th>
				<td><?=$cfg['card_site_name']?></td>
			</tr>
			<tr>
				<th scope="row">신용카드<br>부분취소 사용</th>
				<td>
					<label class="p_cursor"><input type="radio" name="kcp_part_cancel" value="Y" <?=checked($cfg['kcp_part_cancel'], 'Y')?>> 사용함</label>
					<label class="p_cursor"><input type="radio" name="kcp_part_cancel" value="N" <?=checked($cfg['kcp_part_cancel'], 'N').checked($cfg['kcp_part_cancel'], '')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>신용카드 부분취소 사용 설정 시 반드시 KCP와 부분취소 계약 여부를 확인하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">에스크로 배송소요일</th>
				<td>
					<select name="escrow_deli_term">
						<option value="">:: 설정 ::</option>
						<?php for($i = 1; $i <= 30; $i++) { $i = sprintf('%02d', $i); ?>
						<option value="<?=$i?>" <?=checked($cfg['escrow_deli_term'], $i, true)?>><?=$i?> 일</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">복합과세 사용</th>
				<td>
					<label><input type="radio" name="kcp_use_taxfree" value="Y" <?=checked($cfg['kcp_use_taxfree'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="kcp_use_taxfree" value="N" <?=checked($cfg['kcp_use_taxfree'], 'N')?>> 사용안함</label>
					<ul class="list_info tp">
						<li>판매하는 상품의 일부가 면세 상품일 경우 사용할 수 있습니다.</li>
						<li>KCP에 복합과세 신청이 된 상점만 이용 가능합니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- 토스페이먼츠 -->
<?php if ($cfg['card_pg'] == 'dacom') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="dacom">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_test" value="Y" <?=checked($cfg['card_test'],"Y")?>> 테스트</label>
					<label class="p_cursor"><input type="radio" name="card_test" value="N" <?=checked($cfg['card_test'],"N")?>> 실결제</label>
					<?php if(getIsCardTest('pc')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
						<option value="<?=$ii?>" <?=checked($ii,$cfg['card_quotaopt'],1)?>><?=$ii?></option>
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
					<label class="p_cursor"><input type="radio" name="pg_version" value="XpayNon" <?=checked($cfg['pg_version'],"XpayNon")?>> Xpay (NonActiveX)</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_dacom_id']?></td>
			</tr>
			<tr>
				<th scope="row">상점 키값(Mert Key)</th>
				<td><?=$cfg['card_dacom_key']?></td>
			</tr>
			<tr>
				<th scope="row">페이나우</th>
				<td>
					<!-- <label><input type="radio" name="xpay_use_paynow" value="N" <?=checked($cfg['xpay_use_paynow'], 'N')?>> PG 결제창 내에서 선택</label> -->
					<label><input type="checkbox" name="xpay_use_paynow" value="Y" <?=checked($cfg['xpay_use_paynow'], 'Y')?>> 결제 선택시 Paynow 추가</label>
				</td>
			</tr>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- 올앳 -->
<?php if ($cfg['card_pg'] == 'allat') { ?>
<form name="cardPGFrm2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return checkAllat(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="allat">
	<input type="hidden" name="config_code" value="card_pg">

	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_test" value="Y" <?=checked($cfg['card_test'],"Y")?>> 테스트</label>
					<label class="p_cursor"><input type="radio" name="card_test" value="N" <?=checked($cfg['card_test'],"N")?>> 실결제</label>
					<?php if (getIsCardTest('pc')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">무이자 할부</th>
				<td>
					<label class="p_cursor"><input type="radio" id="card_no_interest" name="card_no_interest" value="N" <?=checked($cfg['card_no_interest'],"N")?>> 사용안함</label>
					<label class="p_cursor"><input type="radio" name="card_no_interest" value="Y" <?=checked($cfg['card_no_interest'],"Y")?>> 사용</label>
					<label class="p_cursor"><input type="radio" name="card_no_interest" value="D" <?=checked($cfg['card_no_interest'],"D")?>> 상점속성</label>
				</td>
			</tr>
			<tr>
				<th scope="row">무이자 할부 설정</th>
				<td>
					<label class="p_cursor"><input type="radio" id="card_no_interest_mtype1" name="card_no_interest_mtype" value="1" onClick="cardPG()" <?=checked($cfg['card_no_interest_mtype'],1)?>> 개월 설정</label>
					<label class="p_cursor"><input type="radio" id="card_no_interest_mtype2" name="card_no_interest_mtype" value="2" onClick="cardPG()" <?=checked($cfg['card_no_interest_mtype'],2)?>> 기간 설정</label>

					<div id="mtype1" style="display:none">
						<?=dateSelectBox(2,12,"card_no_interest_month1",$cfg['card_no_interest_month1'])?> 개월 ~
						<?=dateSelectBox(2,12,"card_no_interest_month2",$cfg['card_no_interest_month2'])?> 개월
					</div>
					<div id="mtype2" style="display:none">
						<?php for($ii=2; $ii<=12; $ii++) { ?>
						<label class="p_cursor"><input type="checkbox" id="card_no_interest_month_tmp" name="card_no_interest_month_tmp" value="<?=addZero($ii)?>" <?=checked(preg_match('/@'.addZero($ii).'/', $cfg['card_no_interest_month']), true)?>> <?=$ii?>개월</label>
						<?php } ?>
						<input type="hidden" name="card_no_interest_month" value="">
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">무이자 할부 카드 설정</th>
				<td>
					<?php foreach($_card_cmp as $key=>$val) { ?>
						<label class="p_cursor"><input type="checkbox" name="card_no_interest_cmp_tmp" value="<?=$key?>" <?=checked(preg_match('/@'.$key.'/', $cfg['card_no_interest_cmp']), true)?>> <?=$val?></label>
					<?php } ?>
					<input type="hidden" name="card_no_interest_cmp" value="">
				</td>
			</tr>
			<tr>
				<th scope="row">파트너아이디</th>
				<td><?=$cfg['card_partner_id']?></td>
			</tr>
			<tr>
				<th scope="row">Form Key</th>
				<td><?=$cfg['card_form_key']?></td>
			</tr>
			<tr>
				<th scope="row">Cross Key</th>
				<td><?=$cfg['card_cross_key']?></td>
			</tr>
			<?php if ($cfg['mobile_use'] != 'Y'){ ?>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- KG 이니시스 -->
<?php if ($cfg['card_pg'] == 'inicis') { ?>
<form name="cardPGFrm3" enctype="multipart/form-data" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="inicis">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">실행 모드</th>
				<td>
					<label class="p_cursor"><input type="radio" name="card_test" value="Y" <?=checked($cfg['card_test'],"Y")?>> 테스트</label>
					<label class="p_cursor"><input type="radio" name="card_test" value="N" <?=checked($cfg['card_test'],"N")?>> 실결제</label>
					<?php if (getIsCardTest('pc')) { ?>
					<span class="msg_bubble warning">테스트 모드인 경우 실결제가 이루어지지 않습니다. </span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_quotaopt">
						<?php for ($ii=0; $ii<=12; $ii++) { ?>
						<option value="<?=$ii?>" <?=checked($ii,$cfg['card_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<?php if($cfg['pg_version'] == 'INILite') { ?>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_inicis_id']?></td>
			</tr>
			<tr>
				<th scope="row">signkey</th>
				<td><?=$cfg['card_inicis_key']?></td>
			</tr>
			<?php } else if($cfg['pg_version'] == 'INIweb') { ?>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_web_id']?></td>
			</tr>
            <?php if (empty($cfg['inicis_GID']) == true) { ?>
			<tr>
				<th scope="row">키 패스워드</th>
				<td><?=(empty($cfg['card_web_key_password']) == true) ? '미입력' : '입력완료'?></td>
			</tr>
			<tr>
				<th scope="row">API Key</th>
				<td><?=(empty($cfg['iniweb_basic_apikey']) == true) ? '미입력' : '입력완료'?></td>
			</tr>
			<tr>
				<th scope="row">에스크로 ID</th>
				<td><?=$cfg['escrow_web_id']?></td>
			</tr>
			<tr>
				<th scope="row">에스크로 signkey</th>
				<td><?=(empty($cfg['escrow_web_key']) == true) ? '미입력' : '입력완료'?></td>
			</tr>
			<tr>
				<th scope="row">에스크로 API Key</th>
				<td><?=(empty($cfg['iniweb_escrow_apikey']) == true) ? '미입력' : '입력완료'?></td>
			</tr>
            <?php } ?>
			<?php } else { ?>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['card_mall_id']?></td>
			</tr>
			<tr>
				<th scope="row">키 패스워드</th>
				<td><?=$cfg['card_key_password']?></td>
			</tr>
			<tr>
				<th scope="row">에스크로 ID</th>
				<td><?=$cfg['escrow_mall_id']?></td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">사이트 URL</th>
				<td><?=$cfg['card_site_url']?></td>
			</tr>
			<?php if ($cfg['pg_version'] != 'INILite') { ?>
			<tr>
				<th scope="row">키파일 업로드</th>
				<td>
					<input type="file" name="inicis_key" class="input" size="40">
					<ul class="list_info tp">
						<li>이니시스에서 메일로 받은 키파일(zip) 원본을 업로드하시기 바랍니다.</li>
						<li>업로드하는 파일명이 반드시 발급받은 상점 ID와 일치되어야 합니다.</li>
						<li>상점 키파일과 에스크로 키파일 2개를 2회에 걸쳐 각각 업로드하시기 바랍니다. (상점 키파일 업로드 > [확인], 에스크로 키파일 업로드 > [확인])</li>
					</ul>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- 올더게이트 -->
<?php if ($cfg['card_pg'] == 'allthegate') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="allthegate">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="card_quotaopt">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
						<option value="<?=$ii?>" <?=checked($ii,$cfg['card_quotaopt'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['allthegate_StoreId']?></td>
			</tr>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- KS 페이 -->
<?php if ($cfg['card_pg'] == 'kspay') { ?>
<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="kspay">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="kspay_installrange">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
							<option value="<?=$ii?>" <?=checked($ii,$cfg['kspay_installrange'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">상점 ID</th>
				<td><?=$cfg['kspay_storeid']?></td>
			</tr>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		<tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>
<!-- NICE PAY -->
<?php if ($cfg['card_pg'] == 'nicepay') { ?>
<div class="box_middle left">
	<ul class="list_info">
		<li>
			<p class="title">[가상계좌 자동입금확인 설정]</p>
			[ 나이스페이 상점관리자 &gt; 가맹점정보 &gt; 기본정보 &gt; 결제데이터 통보 - 가상계좌 ] 항목에 아래 주소를 추가해주시기 바랍니다.<br>
			<span class="clipboard" data-clipboard-text="<?=$root_url?>/main/exec.php?exec_file=card.nicepay/vbank.exe.php"><?=$root_url?>/main/exec.php?exec_file=card.nicepay/vbank.exe.php</span>
		</li>
		<li>
			<p class="title">[거래취소비밀번호 설정]</p>
				[ 나이스페이 상점관리자 &gt; 가맹점정보 &gt; 비밀번호관리 &gt; 거래취소비밀번호 ] 경로에서 비밀번호 설정 및 저장한 거래취소비밀번호를 기재해주시기 바랍니다.
			</p>
		</li>
	</ul>
</div>
<form name="cardPGFrm7" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="nicepay">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">최대 할부 개월</th>
				<td>
					<select name="nicepay_installrange">
						<?php for($ii=0; $ii<=12; $ii++) { ?>
							<option value="<?=$ii?>" <?=checked($ii,$cfg['nicepay_installrange'],1)?>><?=$ii?></option>
						<?php } ?>
					</select>
					<ul class="list_info tp">
						<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">상점아이디</th>
				<td><?=$cfg['nicepay_mid']?></td>
			</tr>
			<tr>
				<th scope="row">상점키</th>
				<td><?=$cfg['nicepay_licenseKey']?></td>
			</tr>
			<tr>
				<th scope="row">거래취소비밀번호</th>
				<td><input type="password" name="nicepay_pwd" value="<?=inputText($cfg['nicepay_pwd'])?>" class="input" maxlength="10"></td>
			</tr>
			<tr>
				<th scope="row">복합과세 사용</th>
				<td>
					<label><input type="radio" name="nice_use_taxfree" value="Y" <?=checked($cfg['nice_use_taxfree'], 'Y')?>> 사용함</label>
					<label><input type="radio" name="nice_use_taxfree" value="N" <?=checked($cfg['nice_use_taxfree'], 'N')?>> 사용안함</label>
					<ul class="list_msg">
						<li>상품의 일부가 면세 상품일 경우 사용하실수 있습니다.</li>
						<li>NICEPAY에 복합과세 <u>신청이 된</u> 상점만 이용 가능합니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">모바일 결제</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile_pg_use" value="Y" <?=checked($cfg['mobile_pg_use'], 'Y')?>> 모바일 기기에서 PC 화면으로 결제 시 모바일 PG 사용</label>
					<ul class="list_info tp">
						<li>모바일 결제를 사용 시 PG연동 설정 이후 아래 모바일 PG연동 설정을 하시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>

<?php if ($cfg['card_pg'] == 'eximbay') { ?>
<form name="cardPGFrm8" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="card_pg" value="eximbay">
	<input type="hidden" name="config_code" value="card_pg">
	<table class="tbl_row">
		<caption class="hidden">결제 설정</caption>
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
				<td><?=$cfg['eximbay_mall_id1']?></td>
			</tr>
			<tr>
				<th scope="row">가맹점 Secret Key</th>
				<td><?=$cfg['eximbay_secret_key1']?></td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>

<script type="text/javascript">
	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});

	function cardPG(pg){
		if($('input[name=card_pg]').val() == 'allat') {
			if(document.cardPGFrm2.card_no_interest_mtype.value == '1') {
				$('#mtype1').show();
				$('#mtype2').hide();
			} else {
				$('#mtype1').hide();
				$('#mtype2').show();
			}
		}
	}

	function checkAllat() {
		var f2 = document.cardPGFrm2;
		var month = '';
		for(i = 0; i < f2.card_no_interest_month_tmp.length; i++) {
			if(f2.card_no_interest_month_tmp[i].checked == true) {
				month += '@'+f2.card_no_interest_month_tmp[i].value;
			}
		}
		f2.card_no_interest_month.value = month;

		var cmp = '';
		for(i=0; i<f2.card_no_interest_cmp_tmp.length; i++) {
			if(f2.card_no_interest_cmp_tmp[i].checked == true) {
				cmp += '@'+f2.card_no_interest_cmp_tmp[i].value;
			}
		}
		f2.card_no_interest_cmp.value = cmp;

        printLoading();
	}
</script>