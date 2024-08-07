<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  PG연동 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['card_pg']=="kcp" && $cfg['card_test']!="_test" && $cfg['card_test']!="") $cfg['card_test']="_test";
	if(!$cfg['kcp_use_taxfree']) $cfg['kcp_use_taxfree'] = 'N';
	if(!$cfg['nice_use_taxfree']) $cfg['nice_use_taxfree'] = 'N';
	if(isset($cfg['inicis_GID']) == false) $cfg['inicis_GID'] = 'HOSTwisaG1';
    $scfg->def('eximbay_real_stat1', 'TEST');

	${'card_pg_'.$cfg['card_pg']} = 'on';

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
<div id="select_pg" class="box_tab first">
	<ul>
		<li class="tab_dacom"><a href="#" onclick="cardPG('dacom'); return false;">토스페이먼츠<span class="toggle <?=$card_pg_dacom?>"><?=strtoupper($card_pg_dacom)?></span></a></li>
		<li class="tab_kcp"><a href="#" onclick="cardPG('kcp'); return false;">NHN KCP<span class="toggle <?=$card_pg_kcp?>"><?=strtoupper($card_pg_kcp)?></span></a></li>
		<li class="tab_inicis"><a href="#" onclick="cardPG('inicis'); return false;">KG 이니시스<span class="toggle <?=$card_pg_inicis?>"><?=strtoupper($card_pg_inicis)?></span></a></li>
		<li class="tab_nicepay"><a href="#" onclick="cardPG('nicepay'); return false;">NICE PAY<span class="toggle <?=$card_pg_nicepay?>"><?=strtoupper($card_pg_nicepay)?></span></a></li>
		<li class="tab_allat"><a href="#" onclick="cardPG('allat'); return false;">KG 올앳<span class="toggle <?=$card_pg_allat?>"><?=strtoupper($card_pg_allat)?></span></a></li>
		<?php if ($cfg['card_pg'] == 'allthegate') { ?>
		<li class="tab_allthegate"><a href="#" onclick="cardPG('allthegate')">올더게이트<span class="toggle <?=$card_pg_allthegate?>"><?=strtoupper($card_pg_allthegate)?></span></a></li>
		<?php } ?>
		<?php if ($cfg['card_pg'] == 'kspay') { ?>
		<li class="tab_kspay"><a href="#" onclick="cardPG('kspay')">KSPAY<span class="toggle <?=$card_pg_kspay?>"><?=strtoupper($card_pg_kspay)?></span></a></li>
		<?php } ?>
        <li class="tab_eximbay"><a href="#" onclick="cardPG('eximbay')">EXIMBAY<span class="toggle <?=$card_pg_eximbay?>"><?=strtoupper($card_pg_eximbay)?></span></a></li>
	</ul>
</div>

<div id="card1" style="display:<?=$cfg['card_pg']=='kcp'?'':'none'?>">
	<form name="cardPGFrm1" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="kcp">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
				<li>
					가상계좌 주문시 자동 입금확인되려면 아래 경로에서 설정해주시기 바랍니다.<br>
					KCP 상점관리자 > 상점 정보관리 > 정보 변경 > 공통 URL 정보 [공통URL 변경 후] 입력란에 아래 URL을 입력해 주시기 바랍니다.<br>
					<strong><?=$root_url?>/main/exec.php?exec_file=vbank/return.php</strong>
				</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">NHN KCP 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tbody>
				<tr>
					<th scope="row">실행 모드</th>
					<td>
						<label class="p_cursor"><input type="radio" name="card_test" value="_test" <?=checked($cfg['card_test'],"_test")?>> 테스트</label>
						<label class="p_cursor"><input type="radio" name="card_test" value="" <?=checked($cfg['card_test'],"")?>> 실결제</label>
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
					<th scope="row">사이트 코드</th>
					<td>
						<input type="text" name="card_site_cd" value="<?=inputText($cfg['card_site_cd'])?>" class="input">
						<ul class="list_info tp">
							<li>사이트 코드는 대문자로 입력하시기 바랍니다.</li>
							<li><strong>WE</strong> 또는 <strong>WS</strong>로 시작하는 사이트 코드만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 사이트 코드가 <strong>WE</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">사이트 키</th>
					<td><input type="text" name="card_site_key" value="<?=inputText($cfg['card_site_key'])?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">사이트 명</th>
					<td>
						<input type="text" name="card_site_name" value="<?=inputText($cfg['card_site_name'])?>" class="input">
						<ul class="list_info tp">
							<li>사이트명은 반드시 <strong>영문</strong>으로 입력하시기 바랍니다.</li>
						</ul>
					</td>
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
</div>

<?PHP

	// KG 올앳
	if($cfg['card_pg']=="allat" && $cfg['card_test']!="Y" && $cfg['card_test']!="N") $cfg['card_test']="Y";
	if(!$cfg['card_no_interest']) {
		$cfg['card_no_interest']="D";
	}
	if(!$cfg['card_no_interest_mtype']) {
		$cfg['card_no_interest_mtype']=1;
	}

?>
<div id="card2" style="display:<?=$cfg['card_pg']=='allat'?'':'none'?>">
	<form name="cardPGFrm2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkAllat()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="allat">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">KG 올앳 설정</caption>
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
						<label class="p_cursor"><input type="radio" name="card_no_interest" value="Y" <?=checked($cfg['card_no_interest'],"Y")?>> 사용함</label>
						<label class="p_cursor"><input type="radio" id="card_no_interest" name="card_no_interest" value="N" <?=checked($cfg['card_no_interest'],"N")?>> 사용안함</label>
						<label class="p_cursor"><input type="radio" name="card_no_interest" value="D" <?=checked($cfg['card_no_interest'],"D")?>> 상점속성</label>
						<ul class="list_info tp">
							<li>KG 올앳 상점 관리자에서 무이자 설정을하여야 사용할 수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">무이자 할부 설정</th>
					<td>
						<label class="p_cursor"><input type="radio" id="card_no_interest_mtype1" name="card_no_interest_mtype" value="1" onClick="cardPG('allat')" <?=checked($cfg['card_no_interest_mtype'],1)?>> 개월 설정</label>
						<label class="p_cursor"><input type="radio" id="card_no_interest_mtype2" name="card_no_interest_mtype" value="2" onClick="cardPG('allat')" <?=checked($cfg['card_no_interest_mtype'],2)?>> 기간 설정</label>

						<div id="mtype1" style="display:none">
							<?=dateSelectBox(2,12,"card_no_interest_month1",$cfg['card_no_interest_month1'])?> 개월 ~ <?=dateSelectBox(2,12,"card_no_interest_month2",$cfg['card_no_interest_month2'])?> 개월
						</div>
						<div id="mtype2" style="display:none">
							<?php for($ii=2; $ii<=12; $ii++) { ?>
								<label class="p_cursor"><input type="checkbox" name="card_no_interest_month_tmp" value="<?=addZero($ii)?>" <?=checked(preg_match('/@'.addZero($ii).'/', $cfg['card_no_interest_month']), true)?>> <?=$ii?>개월</label>
							<?php } ?>
							<input type="hidden" name="card_no_interest_month" value="">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">무이자 할부 카드 설정</th>
					<td>
						<?php foreach($_card_cmp as $key=>$val) { ?>
							<label class="p_cursor"><input type="checkbox" name="card_no_interest_cmp_tmp" value="<?=$key?>" <?=checked(preg_match('/@'.$key.'/',$cfg['card_no_interest_cmp']),true)?>> <?=$val?></label>
						<?php } ?>
						<input type="hidden" name="card_no_interest_cmp" value="">
					</td>
				</tr>
				<tr>
					<th scope="row">파트너아이디</th>
					<td>
						<input type="text" name="card_partner_id" value="<?=inputText($cfg['card_partner_id'])?>" class="input input_full">
						<ul class="list_info tp">
							<li><strong>WS_</strong>로 시작하는 파트너아이디만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 파트너아이디가 <strong>WS_</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">Form Key</th>
					<td><input type="text" name="card_form_key" value="<?=inputText($cfg['card_form_key'])?>" class="input input_full"></td>
				</tr>
				<tr>
					<th scope="row">Cross Key</th>
					<td><input type="text" name="card_cross_key" value="<?=inputText($cfg['card_cross_key'])?>" class="input input_full"></td>
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
</div>

<?PHP

	// KG 이니시스
	if($cfg['card_pg']=="inicis" && $cfg['card_test']!="Y" && $cfg['card_test']!="N") {
		$cfg['card_test']="Y";
	}

?>
<div id="card3" style="display:<?=$cfg['card_pg']=='inicis'?'':'none'?>">
	<form name="cardPGFrm3" method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="inicis">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
				<li>
					가상계좌 주문시 자동 입금확인되려면 아래 경로에서 설정해주시기 바랍니다.<br>
					이니시스 상점관리자 [ 승인 > 가상계좌 > 입금 통보방식 메뉴] 해당 경로에서 아래 설정을 하시기 바랍니다.<br>
					입금내역통보 URL(IP) : <strong><?=$root_url?>/main/exec.php?exec_file=card.inicis/vbank_input.php</strong>
				</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">KG 이니시스 설정</caption>
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
						<label class="p_cursor"><input type="radio" name="pg_version" onclick="inipayChk()" value="" <?=checked($cfg['pg_version'],"")?>> TX</label>
						<label class="p_cursor"><input type="radio" name="pg_version" onclick="inipayChk()" value="INILite" <?=checked($cfg['pg_version'],"INILite")?>> INILite</label>
						<label class="p_cursor"><input type="radio" name="pg_version" onclick="inipayChk()" value="INIweb" <?=checked($cfg['pg_version'],"INIweb")?>> 웹표준</label>
					</td>
				</tr>
				<tr class="INIweb">
					<th scope="row">상점 ID</th>
					<td>
						<select name="inicis_GID" onchange="inipayChk();">
							<option value="">그룹 없음</option>
							<option value="HOSTwisaG1" <?=checked($cfg['inicis_GID'], 'HOSTwisaG1', true)?>>클라우드버전(임대형)</option>
							<option value="HOSTwisaG2" <?=checked($cfg['inicis_GID'], 'HOSTwisaG2', true)?>>전문가버전(독립형) 비즈니스</option>
							<option value="HOSTwisaG3" <?=checked($cfg['inicis_GID'], 'HOSTwisaG3', true)?>>전문가버전(독립형) 프리미엄</option>
						</select>
						<input type="text" name="card_web_id" value="<?=inputText($cfg['card_web_id'])?>" class="input">
						<ul class="list_info tp">
							<li class="warning">계약한 상점그룹과 상점ID를 잘못 선택하거나 입력할 경우 실결제가 이루어지지 않습니다.</li>
							<li><strong>WD</strong> 또는 <strong>WS</strong>로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WD</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">signkey</th>
					<td><input type="text" name="card_web_key" value="<?=inputText($cfg['card_web_key'])?>" class="input"></td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">키 패스워드</th>
					<td><input type="text" name="card_web_key_password" value="<?=inputText($cfg['card_web_key_password'])?>" class="input"></td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">API Key</th>
					<td>
						<input type="text" name="iniweb_basic_apikey" value="<?=inputText($cfg['iniweb_basic_apikey'])?>" class="input">
						<ul class="list_info tp">
							<li>이니시스 상점관리자의 <strong>상점정보 > 계약정보 > 부가정보 > INIAPI KEY</strong> 메뉴에서 발급받으실 수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">에스크로 ID</th>
					<td><input type="text" name="escrow_web_id" value="<?=inputText($cfg['escrow_web_id'])?>" class="input"></td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">에스크로 signkey</th>
					<td>
						<input type="text" name="escrow_web_key" value="<?=inputText($cfg['escrow_web_key'])?>" class="input">
						<ul class="list_info tp">
							<li>이니시스 상점관리자에서 <strong>에스크로 아이디</strong>로 로그인해서 확인할 수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">에스크로 API Key</th>
					<td>
						<input type="text" name="iniweb_escrow_apikey" value="<?=inputText($cfg['iniweb_escrow_apikey'])?>" class="input">
						<ul class="list_info tp">
							<li>이니시스 상점관리자의 <strong>상점정보 > 계약정보 > 부가정보 > INIAPI KEY</strong> 메뉴에서 발급받으실 수 있습니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INILite">
					<th scope="row">상점 ID</th>
					<td>
						<input type="text" name="card_inicis_id" value="<?=inputText($cfg['card_inicis_id'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>WD</strong> 또는 <strong>WS</strong>로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WD</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INILite">
					<th scope="row">signkey</th>
					<td><input type="text" name="card_inicis_key" value="<?=inputText($cfg['card_inicis_key'])?>" class="input"></td>
				</tr>
				<tr class="INIpay4116">
					<th scope="row">상점 ID</th>
					<td>
						<input type="text" name="card_mall_id" value="<?=inputText($cfg['card_mall_id'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>WD</strong> 또는 <strong>WS</strong>로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WD</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INIpay4116">
					<th scope="row">키 패스워드</th>
					<td><input type="text" name="card_key_password" value="<?=inputText($cfg['card_key_password'])?>" class="input"></td>
				</tr>
				<tr class="INIpay4116">
					<th scope="row">에스크로 ID</th>
					<td><input type="text" name="escrow_mall_id" value="<?=inputText($cfg['escrow_mall_id'])?>" class="input"></td>
				</tr>
				<tr class="INIpay4116">
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
				<tr>
					<th scope="row">사이트 URL</th>
					<td>
						<input type="text" name="card_site_url" value="<?=inputText($cfg['card_site_url'])?>" class="input input_full">
						<ul class="list_info tp">
							<li>사이트 명은 반드시 영문으로 입력하시기 바랍니다.</li>
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
</div>

<?php
	// 토스페이먼츠
	if($cfg['card_pg']=="dacom" && $cfg['card_test']!="Y" && $cfg['card_test']!="N") {
		$cfg['card_test']="Y";
	}
?>
<div id="card4" style="display:<?=$cfg['card_pg']=='dacom'?'':'none'?>">
	<form name="cardPGFrm4" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="dacom">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">토스페이먼츠 설정</caption>
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
					<td>
						<input type="text" name="card_dacom_id" value="<?=inputText($cfg['card_dacom_id'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>ws_</strong>또는 <strong>wp_</strong>로 시작하는 상점 ID만 입력하실수 있습니다.</li>
							<li>위사롤 통해 아이디를 발급 받았으나 코드가 <strong>ws_</strong>또는 <strong>wp_</strong>로 시작되지 않을 경우 1:1고객센터 문의 글로 접수 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 키값(Mert Key)</th>
					<td><input type="text" name="card_dacom_key" value="<?=inputText($cfg['card_dacom_key'])?>" class="input input_full"></td>
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
				<!--
				<tr>
					<td colspan="2">
						<ul>
							<li>유플러스 PG 를 승인누락없이 안정적으로 사용하시려면 PG 관리자에서 다음과 같이 설정해 주십시오.</li>
							<li>[계약정보] - [상점정보관리] 메뉴의 '신용카드 설정정보' 항목에서 '승인 결과 전송 여부'를 '전송(웹전송연동) 으로 변경</li>
							<li>Xpay 2.0 을 사용하실 경우에는 Xpay 2.0 으로 변경해 주십시오.</li>
						</ul>
						<img src="<?=$engine_url?>/_manage/image/config/dacom_note.gif">
					</td>
				</tr>
				-->
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>

<div id="card5" style="display:<?=$cfg['card_pg']=='allthegate'?'':'none'?>">
	<form name="cardPGFrm3" method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="allthegate">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">올더게이트 설정</caption>
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
					<td>
						<input type="text" name="allthegate_StoreId" value="<?=inputText($cfg['allthegate_StoreId'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>WM</strong>으로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WM</strong>으로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
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
</div>

<div id="card6" style="display:<?=$cfg['card_pg']=='kspay'?'':'none'?>">
	<form name="cardPGFrm3" method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="kspay">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">KS 페이 설정</caption>
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
								<option value="<?=$ii?>" <?=checked($cfg['kspay_installrange'], $ii, true)?>><?=$ii?></option>
							<?php } ?>
						</select>
						<ul class="list_info tp">
							<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 ID</th>
					<td><input type="text" name="kspay_storeid" value="<?=inputText($cfg['kspay_storeid'])?>" class="input"></td>
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
</div>

<div id="card7" style="display:<?=$cfg['card_pg']=='nicepay'?'':'none'?>">
	<form method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="nicepay">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
				<li>
					<p class="title">[가상계좌 자동입금확인 설정]</p>
					[ 나이스페이 상점관리자 &gt; 가맹점정보 &gt; 기본정보 &gt; 결제데이터 통보 - 가상계좌 ] 항목에 아래 주소를 추가해주시기 바랍니다.<br>
					<span class="clipboard" data-clipboard-text="<?=$root_url?>/main/exec.php?exec_file=card.nicepay/vbank.exe.php"><?=$root_url?>/main/exec.php?exec_file=card.nicepay/vbank.exe.php</span>
				</li>
				<li>
					<p class="title">[거래취소비밀번호 설정]</p>
					[ 나이스페이 상점관리자 &gt; 가맹점정보 &gt; 비밀번호관리 &gt; 거래취소비밀번호 ] 경로에서 비밀번호 설정 및 저장한 거래취소비밀번호를 기재해주시기 바랍니다.
				</li>
			</ul>
		</div>
		<table class="tbl_row">
			<caption class="hidden">NICE PAY 설정</caption>
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
								<option value="<?=$ii?>" <?=checked($cfg['nicepay_installrange'], $ii, true)?>><?=$ii?></option>
							<?php } ?>
						</select>
						<ul class="list_info tp">
							<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">상점아이디</th>
					<td><input type="text" name="nicepay_mid" value="<?=inputText($cfg['nicepay_mid'])?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">상점키</th>
					<td><input type="text" name="nicepay_licenseKey" value="<?=inputText($cfg['nicepay_licenseKey'])?>" class="input" size="100"></td>
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
						<ul class="list_info tp">
							<li>상품의 일부가 면세 상품일 경우 사용하실수 있습니다.</li>
							<li>NICE PAY에 복합과세 <u>신청이 된</u> 상점만 이용 가능합니다.</li>
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
</div>

<div id="card8" style="display:<?=$cfg['card_pg']=='eximbay'?'':'none'?>">
	<form method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_pg" value="eximbay">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다. (에스크로도 동일하게 적용됩니다.)</li>
		</div>
		<table class="tbl_row">
			<caption class="hidden">EXIMBAY 설정</caption>
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
					<td><input type="text" name="eximbay_mall_id1" value="<?=$cfg['eximbay_mall_id1']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key1" value="<?=$cfg['eximbay_secret_key1']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
    </form>
</div>

<script type="text/javascript">
	function cardPG(pg){
		$('#select_pg').find('.active').removeClass('active');
		$('#select_pg .tab_'+pg+'>a').addClass('active');

		$('div[id^=card]').each(function() {
			if(pg == $(this).find('input[name=card_pg]').val()) {
				this.style.display = '';
			} else {
				this.style.display = 'none';
			}
		});

		if(pg == 'allat') {
			if(document.cardPGFrm2.card_no_interest_mtype.value == '1') {
				$('#mtype1').show();
				$('#mtype2').hide();
			} else {
				$('#mtype1').hide();
				$('#mtype2').show();
			}
		}
		inipayChk();
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

	function inipayChk() {
		if($('#card3').css('display') == 'none') return;

		var pg_version = $('#card3 input[name="pg_version"]:checked').val();
		var inicis_GID = $('select[name=inicis_GID]').val();
		if($('#card3 input[name="pg_version"]:checked').length == 0) {
			pg_version = 'INIweb';
			$(':radio[name=pg_version][value=INIweb]').attrprop('checked', true);
		}

		if(pg_version == 'INILite') {
			$('.INIpay4116').hide();
			$('.INIweb').hide();
			$('.INILite').show();
		} else if(pg_version == 'INIweb') {
			$('.INIpay4116').hide();
			$('.INILite').hide();
			$('.INIweb').show();
			if(inicis_GID == '') $('.noGID').show();
			else $('.noGID').hide();
		} else {
			$('.INILite').hide();
			$('.INIweb').hide();
			$('.INIpay4116').show();
		}
	}

	$(document).ready(function() {
		inipayChk();
		cardPG("<?=$cfg['card_pg']?>");
	});

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});
</script>