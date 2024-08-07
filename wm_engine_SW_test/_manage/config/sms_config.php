<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  고객 윙문자 설정
	' +----------------------------------------------------------------------------------------------+*/

	// 샘플
	$sms_replace['name'] = '홍길동';
	$sms_replace['member_id'] = 'wisa';
	$sms_replace['ono'] = date('Ymd').'-12345';
	$sms_replace['buyer_name'] = '홍길동';
	$sms_replace['pay_type'] = '신용카드';
	$sms_replace['pay_prc'] = '15,000';
	$sms_replace['dlv_name'] = '스마트윙택배';
	$sms_replace['dlv_code'] = '100490';
	$sms_replace['pwd'] = '123456';
	$sms_replace['account'] = '국민은행 123456-04-123456';
	$sms_replace['config_name'] = '설정 > 결제설정 > 적립금설정 > 적립금 적립 기준';
	$sms_replace['admin'] = '홍길동';
	$sms_replace['prd_name'] = '상품';
	$sms_replace['bank_name'] = '홍길동';
	$sms_replace['amount'] = '500';
	$sms_replace['expire_date'] = date('Y/m/d', $now);
	$sms_replace['agree_date'] = date('Y/m/d', $now);
	$sms_replace['agree_receive'] = "SMS수신동의, 이메일 수신거부가 정상적으로 처리되었습니다.";
	$sms_replace['title'] = '샘플상품 외 2건';
	$sms_replace['board_name'] = '상품문의';
	$sms_replace['dlv_date'] = date('Y-m-d', strtotime('+2 days'));
	$sms_replace['first_dlv_date'] = date('Y-m-d', strtotime('+2 days'));
	$sms_replace['cpn_name'] = '할인 쿠폰';
	$sms_replace['cpn_finish_date'] = date('Y-m-d', strtotime('+1 weeks'));
	$sms_replace['milage_amount'] = '3,000';
	$sms_replace['milage_expiration'] = date('Y-m-d', strtotime('+1 weeks'));

	// 재입고 알림
	$sms_replace['notify_restock_prd'] = '샘플상품';
	$sms_replace['notify_restock_opt'] = '색상 : 그레이, 사이즈 : M';
	$sms_replace['trans_date'] = date('Y년 m월 d일', strtotime('+30 days', $now));

	$sadmin = $_GET['sadmin'];

	include_once $engine_dir."/_engine/sms/sms_module.php";
    $_sms_type[1] = $_sms_type[8] = $_sms_type[20] = $_sms_type[21] = $_sms_type[22] = $_sms_type[23] = $_sms_type[28] = $_sms_type[31] = $_sms_type[39] = $_sms_type[41] = 'member';
	$_sms_type[2] = $_sms_type[3] = $_sms_type[9] = $_sms_type[13] = $_sms_type[15] = $_sms_type[26] = $_sms_type[29] = $_sms_type[30] = 'order';
    $_sms_type[4] = $_sms_type[5] = $_sms_type[6] = $_sms_type[14] = 'delivery';
	$_sms_type[16] =  $_sms_type[24] = $_sms_type[25] = $_sms_type[38] = 'promotion';
	$_sms_type[27] = $_sms_type[32] = $_sms_type[33] = $_sms_type[34] = $_sms_type[35] = $_sms_type[36] = $_sms_type[37] = 'subscription';

	if($body == 'member@kakao_amt_reg.exe') return;

	if(!isTable($tbl['alimtalk_template'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['alimtalk_template']);
	}

	 // 카카오 알림톡 사용 여부
	$wec = new weagleEyeClient($_we, 'alimtalk');
	$profile = $wec->call('getProfile');
	$profile = json_decode($profile);
	if($profile -> alimTalk_profile && $profile -> alimTalk_profile == $cfg['alimtalk_profile_key']) {
		$use_alimtalk = 'Y';

		$alimtalk_list = array();
		$res = $pdo->iterator("select no, sms_case, templateCode, templateName from $tbl[alimtalk_template] where reg_status='APR'");
        foreach ($res as $amtdata) {
			$_case = $amtdata['sms_case'];
			$_code = $amtdata['templateCode'];
			$_name = stripslashes($amtdata['templateName']);
			$alimtalk_list[$_case][$_code] = $_name;
		}
	}

	if($sadmin=="Y") {
		$mtitle="관리자 문자 알림 설정";
		if($admin['level'] > 3) $mtitle="입점사 문자알림 설정";
		foreach($sms_case_title as $key => $val) {
			if(in_array($key, $sms_case_admin) == false) unset($sms_case_title[$key]);
		}
		if($admin['level'] > 3) {
			unset($sms_case_title[11]);
			unset($sms_case_title[19]);
			unset($sms_case_title[40]);
		}
	}
	else {
		$mtitle="고객 문자 알림 설정";
		foreach($sms_case_admin as $key => $val) {
			unset($sms_case_title[$val]);
		}
	}

	$mms_callback = MMS_callback();
	$_mms_callback = explode('@', $mms_callback);
	$_mms_callback = array_filter($_mms_callback);

	if (!in_array($cfg['config_sms_send_num'], $_mms_callback)) {
		if ($cfg['config_sms_send_num'] && $_mms_callback[0]) {
			$pdo->query("update {$tbl['config']} set value = '$_mms_callback[0]', edt_date = '$now' where  name = 'config_sms_send_num'");
			$cfg['config_sms_send_num'] = $_mms_callback[0];
		}
	}

	$_mng_push = array(
		'N' => 'SMS로만 수신',
		'Y' => '관리자앱 PUSH로만 수신',
		'A' => '관리자앱 및 SMS로 수신'
	);

	if($cfg['use_partner_shop'] == 'Y') {
		$partner_sms = $pdo->row("select `partner_sms` from `$tbl[partner_shop]` where `no`= '$admin[partner_no]'");
	}

	if(!$cfg['milage_expire_sms_case']) $cfg['milage_expire_sms_case'] = 'A';
	if($cfg['milage_expire_sms_case'] == 'B' ) {
		$disable1 = "disabled";
	} else {
		$disable2 = "disabled";
	}

?>
<script type="text/javascript">
	function checkSMSConfig(f){
		if (f.admin && f.admin.value=="Y") {
			if (f.config_sms_rec && f.config_sms_rec[1].checked==true) {
				if (!checkBlank(f.config_sms_rec_num,"수신 번호를 입력해주세요.")) return false;
			}
		} else {
			if (f.config_sms_send) {
				if (!checkBlank(f.config_sms_send_num,"발신 번호를 입력해주세요.")) return false;
			}
		}
        printLoading();
	}
	function taVisible(obj, max) {
		var height = (obj.scrollHeight > max) ? max : obj.scrollHeight;
		if(height < 100) height = 100;
		obj.style.height = height;
	}
	var callback = new layerWindow('board@callback.exe');
	function updateCallback(f) {
		f.target = hid_frame;
	}
</script>

<?php if (count($_mms_callback) < 1) { ?>
<div class="msg_topbar warning" style="margin-bottom:10px;">
	<strong>발신번호 사전 등록(사전등록제) 실시로 인해 사전 등록되지 않은 발신번호의 SMS발송이 제한됩니다.</strong>
</div>
<?php } ?>
<form id="smsFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkSMSConfig(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="sms_config">

	<div class="box_title first">

	<?php if ($sadmin == "Y") { ?>
		<h2 class="title"><?=$mtitle?></h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden"><?=$mtitle?></caption>
	<?php } else { ?>
		<h2 class="title">
			<?=$mtitle?>
			<?php if (count($_mms_callback) > 0) { ?>
			<div class="btns">
				<span class="box_btn_s"><a href="http://redirect.wisa.co.kr/mms_service2" target="_blank">발신번호 사전등록</a></span>
			</div>
			<?php } ?>
		</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden"><?=$mtitle?></caption>
	<?php } ?>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?php if ($sadmin == "Y") { ?>
			<tr>
				<th scope="row">수신번호</th>
				<td>
					<?php if ($admin['level'] > 3) { ?>
					<input type="text" name="partner_sms" value="<?=$partner_sms?>" class="input input_full">
					<?php } else { ?>
					<label class="p_cursor"><input type="radio" name="config_sms_rec" id="config_sms_rec" value="1" <?=checked($cfg['config_sms_rec'],1).checked($cfg['config_sms_rec'],"")?>> 관리자번호 : <?=$cfg['admin_cell']?></label><br><br>
					<label class="p_cursor"><input type="radio" name="config_sms_rec" id="config_sms_rec" value="2" <?=checked($cfg['config_sms_rec'],2)?>> 지정수신번호</label> : <input type="text" name="config_sms_rec_num" value="<?=$cfg['config_sms_rec_num']?>" class="input input_full">
					<?php } ?>
					<div class="list_info tp">
						<p>수신번호를 추가할 경우 쉼표(,)로 구분하여 입력하세요. 예 : 010-1234-5678,010-5678-1234</p>
					</div>
				</td>
			</tr>
			<?php } else { ?>
			<tr>
				<th scope="row">발신번호</th>
				<td>
					<input type="hidden" name="config_sms_send" value="2">
					<?php
						foreach($_mms_callback as $key => $val) {
							if($cfg['config_sms_send'] == 1) $checked = checked(numberOnly($cfg['company_phone']), $val).checked(numberOnly($cfg['company_phone']), '');
							else $checked = checked(numberOnly($cfg['config_sms_send_num']), $val).checked(numberOnly($cfg['config_sms_send_num']), '');
					?>
					<label><input type="radio" name="config_sms_send_num" value="<?=$val?>" <?=$checked?>> <?=$val?></label><br>
					<?php } ?>
					<?php if (count($_mms_callback) == 0) { ?>
					<span class="box_btn_s"><a href="http://redirect.wisa.co.kr/mms_service2" target="_blank">발신번호 사전등록</a></span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">주문관리 SMS 설정</th>
				<td>
					<label class="p_cursor"><input type="radio" name="order_sms_history" value="N" <?=checked($cfg['order_sms_history'], 'N')?>> 주문상태 변경 시 항상 발송
					<span class="explain">(이전 상태로 돌아갈 경우 재발송)</span></label><br>
					<label class="p_cursor"><input type="radio" name="order_sms_history" value="Y" <?=checked($cfg['order_sms_history'], 'Y')?>> 주문상태 별로 한 번만 발송
					<span class="explain">(이전 상태로 돌아갈 경우 발송안함)</span></label>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">발송제한 설정</th>
				<td>
					<select name="night_sms_start" onchange="setNightType()">
						<option value="">:: 사용안함 ::</option>
						<?php
						for($i = 0; $i <= 23; $i++){
							$selected = "$i" === $cfg['night_sms_start'] ? 'selected' : '';
						?>
						<option value="<?=$i?>" <?=$selected?>><?=$i?>시 00분</option>
						<?php } ?>
					</select> 부터 <span id="night_type"></span>
					<select name="night_sms_end" onchange="setNightType()">
						<option value="">:: 사용안함 ::</option>
						<?php
						for($i = 0; $i <= 23; $i++){
							$selected = "$i" === $cfg['night_sms_end'] ? 'selected' : '';
						?>
						<option value="<?=$i?>" <?=$selected?>><?=$i?>시 59분</option>
						<?php } ?>
					</select> 까지 발송제한 시간으로 설정합니다.
					<p class="explain">
						지정된 시간대에 발송되는 문자의 경우 문자별 발송제한 설정에 따라 다음과 같이 처리됩니다.<br>
						<span class="p_color2">발송제한 시간의 시작시간과 종료시간을 모두 입력해야 작동합니다. 예) 21시 ~ 08시</span>
					</p>
					<ul class="list_info tp">
						<li>즉시발송 : 제한을 두지 않고 바로 발송됩니다. '주문완료시 발송'의 경우 고객이 활동하고 있는 상태이므로, 즉시발송으로 설정하는 것을 추천드립니다.</li>
						<li>예약발송 : 발송제한 시간동안 바로 발송하지 않고, 발송제한 시간이 끝난 이후 발송됩니다. 예) 7시 59분까지가 발송제한 시간일 경우 8시 이후 발송됩니다.)</li>
						<li>발송중단 : 발송제한 시간동안 문자 발송을 중단합니다.</li>
					</ul>
					<script type="text/javascript">
					function setNightType() {
						var f = document.getElementById('smsFrm');
						var sTime = parseInt(f.night_sms_start.value).toNumber();
						var eTime = parseInt(f.night_sms_end.value).toNumber();
						var str = '';

						if(f.night_sms_start.value == '' || f.night_sms_end.value == '') {
							str = '';
						} else if(sTime > eTime) {
							str = '익일';
						} else if(eTime >= sTime) {
							str = '당일';
						}
						$('#night_type').html(str);
					}
					setNightType();
					</script>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="smsFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkSMSConfig(this)">
	<input type="hidden" name="body" value="config@sms_config.exe">
	<div class="box_title">
		<h2 class="title">문자 발송 조건/문구 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_info">
			<li>사용할 항목만 <img src="<?=$engine_url?>/_manage/image/member/sms/icon_check.gif" alt=""> 체크해주세요. (메시지 수정 후 확인 버튼을 누르면, 미리보기에서 확인 가능합니다.)</strong></li>
			<li>메시지란을 비우시고 확인을 누르면 초기 메시지로 복구됩니다.</li>
			<?php if ($sadmin != 'Y') { ?>
				<li>SMS 수신 동의를 한 고객에게만 메시지가 발송됩니다.</li>
			<?php } ?>
		</ul>
	</div>
	<?php if ($sadmin != 'Y') { ?>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class='member'><a href="#" onclick="smsTab('member'); return false;">회원</a></li>
			<li class='order'><a href="#" onclick="smsTab('order'); return false;">주문</a></li>
			<li class='delivery'><a href="#" onclick="smsTab('delivery'); return false;">배송</a></li>
			<li class='promotion'><a href="#" onclick="smsTab('promotion'); return false;">프로모션</a></li>
			<li class='subscription'><a href="#" onclick="smsTab('subscription'); return false;">정기배송</a></li>
		</ul>
	</div>
	<?php } ?>
	<div class="box_middle" style="overflow:hidden;">
		<?php
			foreach($sms_case_title as $key=>$val) {
				$msg1 = SMS_send_case($key, "test1", $admin['partner_no']);
				$msg2 = SMS_send_case($key, "test2", $admin['partner_no']);

				$data = get_info($tbl['sms_case'], "case", $key);
				$disabled = "";
				if($key==22 || $key==28) {
					$disabled = "disabled";
					$data['use_check'] = "Y";
				}
				$border = ($case == $key) ? " style=\"border:1 solid #cc0000;\"" : "";
				if($admin['level'] > 3) {
						$tmp = $pdo->assoc("select * from `wm_partner_sms` where `partner_no`='$admin[partner_no]' and `case` ='$key' ");
						$data['use_check'] = $tmp['use_check'];
						$data['msg'] = $tmp['msg'];
						$data['sms_night'] = $tmp['sms_night'];
						$data['alimtalk_code'] = $tmp['alimtalk_code'];
						$data['case'] = $tmp['case'];
						$data['use_check'] = $tmp['use_check'];
				}
				$type = $_sms_type[$key];
				$idx++;
		?>
			<input type="hidden" name="case[]" value="<?=$key?>">
			<div class="box_sms sms_type_<?=$type?>">
				<div class="state">
					<div class="check">
						<input type="checkbox" id="sms_check<?=$idx?>" name="use_check[<?=$key?>]" value="Y" class="check_img" <?=$disabled?> <?=checked($data['use_check'],"Y")?>><label for="sms_check<?=$idx?>"></label>
					</div>
					<p class="send">
						<?=$sms_case_title[$key]?>
						<?php if ($key == 17 && $admin['level'] < 4) { ?>
						<span class="box_btn_s"><input type="button" value="게시판 별 사용설정" onclick="callback.open();"></span>
						<?php } ?>
					</p>
					<div class="time">
						발송제한 :
						<select name="sms_night[<?=$key?>]">
							<option value="N" <?=checked($data['sms_night'],'N', 1)?>>즉시발송</option>
							<option value="H" <?=checked($data['sms_night'],'H', 1)?>>예약발송</option>
							<option value="Y" <?=checked($data['sms_night'],'Y', 1)?>>발송중단</option>
						</select>
					</div>
				</div>
				<div class="view">
					<?php
						$we_mms = new WeagleEyeClient($GLOBALS['_we'], $cfg['sms_module']);
						$mms_config = $we_mms->call('getMmsConfig');

						for($ii = 1; $ii <= 2; $ii++){
							$_length_msg = ($ii == 2) ? "<b style=\"color:#d62525\">".strlen(iconv('utf-8', 'euc-kr', $msg2))."</b> / ".$mms_config[0]->lms_total_byte[0]." bytes" : "&nbsp;";
							$_tag = ($ii == 2) ? "readonly" : "name=\"msg[".$key."]\"";
							$_title = ($ii == 2) ? "미리보기" : "메세지수정";
					?>
					<div class="frame frame<?=$ii?>">
						<p class="title"><?=$_title?></p>
						<div class="msg bg<?=$data['use_check']?>">
							<textarea id="ta_<?=$idx?>_<?=$ii?>" class="txta_sms" <?=$_tag?>  onkeydown="taVisible(this,190)"><?php if ($ii == 2 && ($key == 21 || $key == 16)) {?>(광고)<?php } ?><?=${"msg".$ii}?><?php if (($key == 21 || $key == 16) && $cfg['use_080sms'] == 'Y' && $ii == 2) { ?> 무료 수신거부 : <?echo $cfg['080_number']?> <?php } ?></textarea>
						</div>
						<div class="left" style="margin-top:5px; min-height:72px;">
							<?php if ($use_alimtalk == 'Y' && is_array($alimtalk_list[$key]) == true && $ii == 1) { ?>
							<select name="equipt_alimtalk[<?=$key?>]" style="max-width:100%; margin-bottom:5px;">
								<option value="">:: 카카오알림톡 템플릿을 선택해주세요 ::</option>
								<?php foreach($alimtalk_list[$key] as $_code => $_name) { ?>
								<option value="<?=$_code?>" <?=checked($_code, $data['alimtalk_code'], true)?>><?=$_name?></option>
								<?php } ?>
							</select>
							<?php } ?>
							<?php if ($sadmin == 'Y' && $ii == 1 && $admin['level'] < 3) {
								echo selectArray($_mng_push, "mng_push[$key]", false, null, $data['mng_push']);
							}?>
						</div>
						<?php if ($ii == 2) { ?>
						<div class="byte"><?=$_length_msg?></div>
						<script type="text/javascript">
							var ta = document.getElementById('ta_<?=$idx?>_<?=$ii?>');
							taVisible(ta,190);
						</script>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
	<div class="box_title">
		<h2 class="title">치환메시지 안내</h2>
	</div>
	<div class="box_bottom top_line">
		<table class="tbl_inner full line">
			<colgroup>
				<col style="width:220px;">
				<col>
			</colgroup>
			<thead>
				<tr>
					<th>항목명</th>
					<th>치환메시지</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($admin['level'] < 3) { ?>
				<tr class="desc_sms sms_type_member">
					<td><strong>회원가입</strong></td>
					<td class="left">{이름} {아이디}</td>
				</tr>
				<?php } ?>
				<tr class="desc_sms sms_type_order">
					<td><strong>주문완료</strong></td>
					<td class="left">
						{주문자} {주문번호} {주문상품명}  {금액} {계좌번호} {배송지}
						<?php if ($admin['level'] < 3) { ?>
							<div class="list_info tp">
								<p>계좌번호는 <u>무통장입금/에스크로결제</u> 고객에게만 표시됩니다.</p>
							</div>
						<?php } ?>
					</td>
				</tr>
				<?php if ($sadmin != 'Y') { ?>
				<tr class="desc_sms sms_type_order">
					<td><strong><?=$_order_stat[2]?></strong></td>
					<td class="left">{주문자} {주문번호} {금액} {배송지}</td>
				</tr>
				<tr class="desc_sms sms_type_delivery">
					<td><strong><?=$_order_stat[3]?></strong></td>
					<td class="left">{주문자} {주문번호} {금액} {배송비} {송장번호} {배송사} {배송지}</td>
				</tr>
				<tr class="desc_sms sms_type_delivery">
					<td><strong><?=$_order_stat[4]?></strong></td>
					<td class="left">{주문자} {주문번호} {주문상품명} {금액} {배송비} {송장번호} {배송사} {배송지}</td>
				</tr>
				<tr class="desc_sms sms_type_delivery">
					<td><strong><?=$_order_stat[5]?></strong></td>
					<td class="left">{주문자} {주문번호} {금액}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong>무통장 주문</strong></td>
					<td class="left">{주문자} {주문번호} {금액} {계좌번호}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong>자동입금확인</strong></td>
					<td class="left">{주문자} {주문번호} {금액} {입금자명}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong>입금요청</strong></td>
					<td class="left">{주문자} {주문번호} {금액} {계좌번호}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong>무통장 자동취소</strong></td>
					<td class="left">{주문자} {주문번호}</td>
				</tr>
				<tr class="desc_sms sms_type_delivery">
					<td><strong>부분 <?=$_order_stat[4]?></strong></td>
					<td class="left">{주문자} {주문번호} {주문상품명} {금액} {배송비} {송장번호} {배송사}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong><?=$_order_stat[15]?></strong></td>
					<td class="left">{주문자} {주문번호} {주문상품명} {금액}</td>
				</tr>
				<tr class="desc_sms sms_type_order">
					<td><strong><?=$_order_stat[17]?></strong></td>
					<td class="left">{주문자} {주문번호} {주문상품명} {금액}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>인증번호발송</strong></td>
					<td class="left">{인증번호}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>상품질문답변</strong></td>
					<td class="left">{이름}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>적립금소멸(정보성/광고성)</strong></td>
					<td class="left">{소멸적립금} {소멸예정일}</td>
				</tr>
				<tr class="desc_sms sms_type_promotion">
					<td><strong><?=$sms_case_title[16]?></strong></td>
					<td class="left">{이름} {아이디}</td>
				</tr>
				<!--
				<tr>
					<td><strong><?=$sms_case_title[17]?></strong></td>
					<td class="left">{게시판명} {게시글제목} {작성자}</td>
				</tr>
				-->
				<tr class="desc_sms sms_type_member">
					<td><strong>광고성정보 수신여부 변경</strong></td>
					<td class="left">{광고성정보변경일자} {SMS이메일수신동의여부}</td>
				</tr>
				<tr class="desc_sms sms_type_promotion">
					<td><strong>재입고 알림 신청</strong></td>
					<td class="left">{재입고상품명} {재입고상품옵션}</td>
				</tr>
				<tr class="desc_sms sms_type_promotion">
					<td><strong>재입고 알림 발송</strong></td>
					<td class="left">{이름} {재입고상품명} {재입고상품옵션}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>휴면회원 사전안내</strong></td>
					<td class="left">{아이디} {휴면처리일}</td>
				</tr>
				<tr class="desc_sms sms_type_subscription">
					<td><strong>주문 완료</strong></td>
					<td class="left">{주문자} {주문번호} {금액} {첫배송일}</td>
				</tr>
				<tr class="desc_sms sms_type_subscription">
					<td><strong>배송시작(정기결제)</strong></td>
					<td class="left">{주문자} {주문번호} {금액} {상품명} {배송예정일}</td>
				</tr>
				<tr class="desc_sms sms_type_subscription">
					<td><strong>배송시작(일괄결제)</strong></td>
					<td class="left">{주문자} {주문번호} {상품명} {배송예정일}</td>
				</tr>
				<tr class="desc_sms sms_type_subscription">
					<td><strong>취소완료</strong></td>
					<td class="left">{주문자} {주문번호}</td>
				</tr>
				<tr class="desc_sms sms_type_subscription">
					<td><strong>회차취소</strong></td>
					<td class="left">{주문자} {주문번호}</td>
				</tr>
                <!--
				<tr class="desc_sms sms_type_subscription">
					<td><strong>품절</strong></td>
					<td class="left">{주문자} {주문번호} {상품명}</td>
				</tr>
                -->
				<tr class="desc_sms sms_type_subscription">
					<td><strong>진행종료</strong></td>
					<td class="left">{주문자} {주문번호}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>개인정보 이용내역 안내</strong></td>
					<td class="left">{이름}</td>
				</tr>
				<tr class="desc_sms sms_type_promotion">
					<td><strong>쿠폰 발급</strong></td>
					<td class="left">{이름} {아이디} {쿠폰명} {쿠폰만료일}</td>
				</tr>
				<tr class="desc_sms sms_type_member">
					<td><strong>적립금 수동 지급</strong></td>
					<td class="left">{이름} {아이디} {지급적립금} {적립금유효기간} {사유}</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td><strong>회원가입</strong></td>
					<td class="left">{이름} {아이디} </td>
				</tr>
				<tr>
					<td><strong>가상계좌,자동입금확인</strong></td>
					<td class="left">{주문번호} {금액}</td>
				</tr>
				<tr>
					<td><strong>신규게시글 작성</strong></td>
					<td class="left">{이름} {아이디} {제목} {게시판명} {상품명}</td>
				</tr>
				<tr>
					<td><strong>관리자설정변경</strong></td>
					<td class="left">{관리자이름} {설정명}</td>
				</tr>
				<tr>
					<td><strong><?=$sms_case_title[40]?></strong></td>
					<td class="left">{이름} {아이디} </td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</form>
<?php if ($sadmin != 'Y') { ?>
<script type="text/javascript">
function smsTab(type) {
	var tab = $('.tab_sort>li');
	tab.removeClass('active');
	tab.filter('.'+type).addClass('active');

	$('.box_sms, .desc_sms').hide();
	$('.sms_type_'+type).show();
}
smsTab('member');
</script>
<?php } ?>