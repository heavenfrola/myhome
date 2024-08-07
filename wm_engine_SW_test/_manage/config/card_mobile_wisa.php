<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  모바일 PG연동 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['card_mobile_pg']=="dacom" && $cfg['card_mobile_test']!="Y" && $cfg['card_mobile_test']!="N") $cfg['card_mobile_test']="Y";
	if($cfg['card_mobile_pg']=="kcp" && $cfg['card_mobile_test']!="_test" && $cfg['card_mobile_test']!="") $cfg['card_mobile_test']="_test";
    $scfg->def('eximbay_real_stat1m', 'TEST');

	${'card_mpg_'.$cfg['card_mobile_pg']} = 'on';

?>

<div class="box_title">
	<h2 class="title">모바일 국내결제 PG 설정</h2>
</div>
<div id="select_mpg" class="box_tab first">
	<ul>
		<li class="tab_dacom"><a href="#" onclick="cardMPG('dacom'); return false;">토스페이먼츠<span class="toggle <?=$card_mpg_dacom?>"><?=strtoupper($card_mpg_dacom)?></span></a></li>
		<li class="tab_kcp"><a href="#" onclick="cardMPG('kcp'); return false;">NHN KCP<span class="toggle <?=$card_mpg_kcp?>"><?=strtoupper($card_mpg_kcp)?></span></a></li>
		<li class="tab_inicis"><a href="#" onclick="cardMPG('inicis'); return false;">KG 이니시스<span class="toggle <?=$card_mpg_inicis?>"><?=strtoupper($card_mpg_inicis)?></span></a></li>
		<li class="tab_nicepay"><a href="#" onclick="cardMPG('nicepay'); return false;">NICE PAY<span class="toggle <?=$card_mpg_nicepay?>"><?=strtoupper($card_mpg_nicepay)?></span></a></li>
		<li class="tab_allat"><a href="#" onclick="cardMPG('allat'); return false;">KG 올앳<span class="toggle <?=$card_mpg_allat?>"><?=strtoupper($card_mpg_allat)?></span></a></li>
		<?php if($cfg['card_mobile_pg'] == 'allthegate') { ?>
		<li class="tab_allthegate"><a href="#" onclick="cardPG('allthegate')">올더게이트<span class="toggle <?=$card_mpg_allthegate?>"><?=strtoupper($card_mpg_allthegate)?></span></a></li>
		<?php } ?>
		<?php if ($cfg['card_mobile_pg'] == 'kspay') { ?>
		<li class="tab_kspay"><a href="#" onclick="cardPG('kspay')">KSPAY<span class="toggle <?=$card_mpg_kspay?>"><?=strtoupper($card_mpg_kspay)?></span></a></li>
		<?php } ?>
		<li class="tab_eximbay"><a href="#" onclick="cardMPG('eximbay'); return false;">EXIMBAY<span class="toggle <?=$card_mpg_eximbay?>"><?=strtoupper($card_mpg_eximbay)?></span></a></li>
	</ul>
</div>

<div id="mcard1">
	<form name="cardMPGFrm1" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="dacom">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
						<label class="p_cursor"><input type="radio" name="card_mobile_use" value="Y" <?=checked($cfg['card_mobile_use'],"Y")?> <?=checked($cfg['card_mobile_use'],"")?>> 사용</label>
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
						<label class="p_cursor"><input type="radio" name="pg_mobile_version" value="smartXpaySubmit" <?=checked($cfg['pg_mobile_version'],"smartXpaySubmit")?>> smartXpaySubmit</label>
					</td>
				</tr>
				<tr>
					<th scope="row">출력 방식</th>
					<td>
						<label class="p_cursor"><input type="radio" name="card_mobile_window" value="" <?=checked($cfg['card_mobile_window'],"")?>> 새창</label>
						<label class="p_cursor"><input type="radio" name="card_mobile_window" value="iframe" <?=checked($cfg['card_mobile_window'],"iframe")?>> 프레임(결제창 하단)</label>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 ID</th>
					<td>
						<input type="text" name="card_mobile_dacom_id" value="<?=inputText($cfg['card_mobile_dacom_id'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>ws_</strong>또는 <strong>wp_</strong>로 시작하는 상점 ID만 입력하실수 있습니다.</li>
							<li>위사롤 통해 아이디를 발급 받았으나 코드가 <strong>ws_</strong>또는 <strong>wp_</strong>로 시작되지 않을 경우 1:1고객센터 문의 글로 접수 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 키값(Mert Key)</th>
					<td><input type="text" name="card_mobile_dacom_key" value="<?=inputText($cfg['card_mobile_dacom_key'])?>" class="input input_full"></td>
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
</div>

<div id="mcard2">
	<form name="cardMPGFrm2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="kcp">
		<input type="hidden" name="config_code" value="card_pg">
		<input type="hidden" name="pg_mobile_version" value="smartPay">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
					<td>
						<input type="text" name="card_mobile_site_cd" value="<?=inputText($cfg['card_mobile_site_cd'])?>" class="input">
						<ul class="list_info tp">
							<li>사이트 코드는 대문자로 입력하시기 바랍니다.</li>
							<li><strong>WE</strong> 또는 <strong>WS</strong>로 시작하는 사이트 코드만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 사이트 코드가 <strong>WE</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">사이트 키</th>
					<td>
						<input type="text" name="card_mobile_site_key" value="<?=inputText($cfg['card_mobile_site_key'])?>" class="input">
					</td>
				</tr>
				<tr>
					<th scope="row">사이트 명</th>
					<td>
						<input type="text" name="card_mobile_site_name" value="<?=inputText($cfg['card_mobile_site_name'])?>" class="input">
						<ul class="list_info tp">
							<li>사이트명은 반드시 <strong>영문</strong>으로 입력하시기 바랍니다.</li>
						</ul>
					</td>
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
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>

<div id="mcard3">
	<form name="cardMPGFrm1" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="inicis">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
					<th scope="row">연동방식</th>
					<td>
						<label class="p_cursor"><input type="radio" name="pg_mobile_version" value="INIpayMobileWeb" <?=checked($cfg['pg_mobile_version'],"INIpayMobileWeb")?>> INIpay Mobile Web</label>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 ID</th>
					<td>
						<input type="text" name="card_inicis_mobile_id" value="<?=inputText($cfg['card_inicis_mobile_id'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>WD</strong> 또는 <strong>WS</strong>로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WD</strong> 또는 <strong>WS</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr class="INIweb noGID">
					<th scope="row">API Key</th>
					<td>
						<input type="text" name="iniweb_mobile_apikey" value="<?=inputText($cfg['iniweb_mobile_apikey'])?>" class="input">
					</td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>

<div id="mcard4">
	<form name="cardMPGFrm3" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="allthegate">
		<input type="hidden" name="pg_mobile_version" value="mobile">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
					<td>
						<input type="text" name="mobile_allthegate_StoreId" value="<?=inputText($cfg['mobile_allthegate_StoreId'])?>" class="input">
						<ul class="list_info tp">
							<li><strong>WM</strong>으로 시작하는 상점ID만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 상점 ID가 <strong>WM</strong>으로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
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

<div id="mcard5">
	<form name="cardMPGFrm5" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="allat">
		<input type="hidden" name="pg_mobile_version" value="mobile">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
					<th scope="row">파트너아이디</th>
					<td>
						<input type="text" name="mobile_card_partner_id" value="<?=inputText($cfg['mobile_card_partner_id'])?>" class="input input_full">
						<ul class="list_info tp">
							<li><strong>WS_</strong>로 시작하는 파트너아이디만 입력할 수 있습니다.</li>
							<li>위사를 통해 발급받은 파트너아이디가 <strong>WS_</strong>로 시작되지 않을 경우 고객센터로 문의 바랍니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">Cross Key</th>
					<td>
						<input type="text" name="mobile_card_cross_key" value="<?=$cfg['mobile_card_cross_key']?>" class="input input_full">
					</td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>

<div id="mcard6">
	<form name="cardMPGFrm6" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="kspay">
		<input type="hidden" name="pg_mobile_version" value="mobile">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
						<select name="kspay_m_installrange">
							<?php for($ii=0; $ii<=12; $ii++) { ?>
								<option value="<?=$ii?>" <?=checked($cfg['kspay_m_installrange'], $ii, true)?>><?=$ii?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">상점 ID</th>
					<td><input type="text" name="kspay_m_storeid" value="<?=inputText($cfg['kspay_m_storeid'])?>" class="input"></td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
	</form>
</div>

<div id="mcard7">
	<form name="cardMPGFrm7" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="nicepay">
		<input type="hidden" name="pg_mobile_version" value="mobile">
		<input type="hidden" name="config_code" value="card_pg">
		<div class="box_middle3 left">
			<ul class="list_info">
				<li class="warning">아래 설정을 변경할 경우 카드 결제가 안될 수 있으므로, 변경 시 유의하시기 바랍니다.</li>
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
						<select name="nicepay_m_installrange">
							<?php for($ii=0; $ii<=12; $ii++) { ?>
								<option value="<?=$ii?>" <?=checked($cfg['nicepay_m_installrange'], $ii, true)?>><?=$ii?></option>
							<?php } ?>
						</select>
						<ul class="list_info tp">
							<li>결제금액이 5만원 이상일 때 할부 가능하며, 0개월로 설정 시 할부가 적용되지 않습니다.</li>
						</ul>
					</td>
				</tr>
				<tr>
					<th scope="row">상점아이디</th>
					<td><input type="text" name="nicepay_m_mid" value="<?=inputText($cfg['nicepay_m_mid'])?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">상점키</th>
					<td><input type="text" name="nicepay_m_licenseKey" value="<?=inputText($cfg['nicepay_m_licenseKey'])?>" class="input" size="100"></td>
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
</div>

<div id="mcard8">
	<form method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
		<input type="hidden" name="body" value="config@config.exe">
		<input type="hidden" name="card_mobile_pg" value="eximbay">
		<input type="hidden" name="pg_mobile_version" value="">
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
						<label><input type="radio" value="TEST" name="eximbay_real_stat1m" <?=checked($cfg['eximbay_real_stat1m'],'TEST')?>> 테스트</label>
						<label><input type="radio" value="LIVE" name="eximbay_real_stat1m" <?=checked($cfg['eximbay_real_stat1m'],'LIVE')?>> 실결제</label>
					</td>
				</tr>
				<tr>
					<th scope="row">가맹점 아이디</th>
					<td><input type="text" name="eximbay_mall_id1m" value="<?=$cfg['eximbay_mall_id1m']?>" class="input"></td>
				</tr>
				<tr>
					<th scope="row">가맹점 Secret Key</th>
					<td><input type="text" name="eximbay_secret_key1m" value="<?=$cfg['eximbay_secret_key1m']?>" class="input input_full"></td>
				</tr>
			</tbody>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="submit" value="확인"></span>
		</div>
    </form>
</div>

<script type="text/javascript">
	function cardMPG(mpg) {
		$('#select_mpg').find('.active').removeClass('active');
		$('#select_mpg .tab_'+mpg+'>a').addClass('active');

		$('div[id^=mcard]').each(function() {
			if(mpg == $(this).find('input[name=card_mobile_pg]').val()) {
				this.style.display = '';
			} else {
				this.style.display = 'none';
			}
		});

		if(mpg == 'dacom') {
			$(':radio[name=pg_mobile_version][value=smartXpaySubmit]').attrprop('checked', true);
		}
	}

	cardMPG("<?=$cfg['card_mobile_pg']?>");
</script>