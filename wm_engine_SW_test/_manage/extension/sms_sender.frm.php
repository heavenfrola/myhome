<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  문자 발송
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/sms/sms_module.php";
	$mms_config = $we_mms->call('getMmsConfig');
	$mms_use_point = explode('/', $mms_config[0]->use_point[0]);

	foreach($_POST as $key => $val) {
		if(is_array($val) == false) $val = addslashes(trim($val));
		${$key} = $_POST[$key] = $val;
	}

	$cell = $_GET['cell'];
	if($exec == 'form_notify_restock' && is_array($check_no)) {
		$check_pno = $check_no;
	}

	if($_POST['msg']) {
		$msg = mb_convert_encoding($_POST['msg'], _BASE_CHARSET_, array('utf8', 'euckr'));
	}

	if(SMS_print(1)) return;
	if($cfg['config_sms_send']=="2") $call_back=$cfg['config_sms_send_num'];
	else $call_back=$cfg['company_phone'];

	// 카카오 친구톡 OTP
	$admin_cell = ($admin['cell']) ? $admin['cell'] : '미설정';

	if($sms_deny) {
		if($sms_deny=="Y") {
			$where=" and `sms`='Y'";
		}
		if($ssmode == 2){
			$check_pno = implode(',', numberOnly($check_pno));
			if($exec == "from_ord") {
				$where.=" and a.`no` in ($check_pno)";
			} else {
			    $where.=" and `no` in ($check_pno)";
			}
		}else if($ssmode == 4){
			$where.=stripslashes($msg_where);
		}

		if($exec == "from_ord"){
			$sql="select distinct a.no, a.buyer_cell as `cell` from `$tbl[order]` a inner join `$tbl[order_product]` c using(ono) where 1 $where";
		} elseif ($exec == 'form_notify_restock') {

			$sql = "select buyer_cell as cell from $tbl[notify_restock] where 1 $where ";
		} else {
			$sql="select no, cell from `$tbl[member]` x where `withdraw` = 'N' $where";
		}

		$res = $pdo->iterator($sql);

		$cell = array();
		$hnum_check=0;
        foreach ($res as $data) {
            $data['cell'] = str_replace('-', '', trim($data['cell']));
			if (!$data['cell']) continue;

			if ($correct_num) { // 올바른 번호만
                if (preg_match('/^(010|011|016|017|018|019)[0-9]{8,9}$/', $data['cell']) == false) {
					$hnum_check++;
					continue;
				}
			}
			$cell[] = $data['cell'];
		}
        $cell = array_unique($cell);
        $tnum = count($tnum);
        $cell = implode("\r\n", $cell);
	}

	$total_point=SMS_rest();
	if($total_point < $mms_use_point[1]){
?>
<script type="text/javascript">
	if(confirm('잔여 포인트가 부족합니다. 충전하시겠습니까?')) {
		goMywisa('?body=wing@main');
	}
	self.close();
</script>
<?
		exit();
	}

	$mms_callback = MMS_callback();
	$_mms_callback = explode('@', $mms_callback);
	$_mms_callback = array_filter($_mms_callback);
	if(count($_mms_callback) < 1) {
?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<div class="popupContent">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop"></div>
	</div>
	<div class="center">
		<p style="padding:20px 0;">
			사전 등록된 발신번호가 없습니다.<br>
			2015년 10월 16일부터는 등록되지 않은 발신번호의 문자발송이 제한됩니다.<br>
			아래 자세히 보기를 통해 발신번호를 등록하세요.
		</p>
		<div><a href="http://redirect.wisa.co.kr/mms_service2" target="_blank"><img src="<?=$engine_url?>/_manage/image/extension/sms/banner.jpg" alt="문자 발신번호 사전등록제 실시 자세히보기"></a></div>
	</div>
</div>
<script type="text/javascript">
	window.onload=function (){
		window.resizeTo(520, 380);
	}
</script>
<?
		exit();
	}
?>
<script type="text/javascript">
	<?if($hnum_check){?>
	alert('부정확한 번호 <?=$hnum_check?> 명를 받는 사람에서 제외시켰습니다');
	<?}?>
</script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/sms.js?20171207"></script>
<div id="sms_status" style="display:none;">
	<table border="0" align="center" width="100%" cellpadding="3" cellspacing="0">
		<tr>
			<td><font color="#336600">SMS를 전송중입니다...</font> <font id="Status_per">0</font>% (<font id="Status_snum">0</font>/<font id="Status_tnum"><?=$tnum?></font>)</td>
		</tr>
		<tr>
			<td><div id="Status" style="width:0%; background-color:#FF9900;"></div></td>
		</tr>
	</table>
</div>
<style type="text/css" title="">
body {min-width:887px; background:#fff;}
</style>
<form method="post" id="phone" name="phone" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return checkSMS(this)" target="hidden<?=$now?>" accept-charset="<?=_BASE_CHARSET_?>">
	<input type="hidden" name="body" value="<?=$_inc[0]?>@sms_sender.exe">
	<input type="hidden" name="show_status" value="Y">
	<input type="hidden" name="sms_total_byte" value="<?=$mms_config[0]->sms_total_byte[0]?>">
	<input type="hidden" name="lms_total_byte" value="<?=$mms_config[0]->lms_total_byte[0]?>">
	<input type="hidden" name="sms_use_point1" value="<?=$mms_use_point[1]?>">
	<input type="hidden" name="sms_use_point2" value="<?=$mms_use_point[2]?>">
	<input type="hidden" name="sms_use_point3" value="<?=$mms_use_point[3]?>">
	<input type="hidden" name="total_point" value="<?=$total_point?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="file_list">
	<div class="tab_sms">
		<ul class="tab_sort">
			<li class="active"><a onclick="tab_sms_view(0)" class="p_cursor active">윙문자</a></li>
			<li><a onclick="tab_sms_view(1)" class="p_cursor">친구톡</a></li>
		</ul>
	</div>
	<div class="sms_sender sms_sender0">
		<p class="explain">
			<span class="icon_info"></span> [서비스 및 차감포인트 안내] SMS 22p / LMS 55p / MMS 264p
		</p>
		<div class="info">
			<table class="tbl_mini full">
				<caption class="hidden">윙문자 발송</caption>
				<colgroup>
					<col style="width:110px">
				</colgroup>
				<tr>
					<th scope="row">잔여 POINT</th>
					<td scope="row"><strong><?=number_format($total_point, 2)?></strong>&nbsp;<span class="box_btn_s"><input type="button" value="충전하기" onClick="goMywisa('?body=wing@sms_charge');"></span></td>
				</tr>
				<tr>
					<th scope="row">전송타입</th>
					<td><span id="sms_type">SMS</span></td>
				</tr>
				<tr>
					<th scope="row">파일첨부 <p class="explain">(<?=number_format($mms_config[0]->max_file_size[0])?> Kbyte)</p></th>
					<td>
						<input type="file" name="upfile" style="width:100%;" class="input" onchange="mmsFileSend(this);"><br>
						<span class="explain icon">이미지(.jpg) 300KB 까지</span>
					</td>
				</tr>
				<tr>
					<th scope="row">파일목록</th>
					<td>
						<b><?=$mms_config[0]->send_file_num[0]?></b> 개까지 전송 가능
						<select size="10" class="input file_list" onclick="filePreview(this);">
						</select>
						<span class="box_btn_s full"><input type="button" value="삭제" onClick="deleteUploadFile(this.form);"></span>
						<p class="explain icon">MMS 메세지의 경우 수신자 이동통신사의 전송 상태에 따라 전송시간이 지연될 수 있으므로 참고해주시기 바랍니다.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">미리보기</th>
					<td><div id="file_preview">선택된 파일이 없습니다</div></td>
				</tr>
			</table>
		</div>
		<div id="sms_phone_body">
			<div class="phone">
				<div id="sms_msg">
					<textarea name="msg" id="msg" onKeyUp="checkByte(this.form, 1); mmsCheck(this.form);"><?=stripslashes($msg)?></textarea>
				</div>
				<div class="byte">
					<input name="msglen" type="text" value="0" size="3" style="border:0; color:red;" dir="rtl" readonly oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
					<input type="text" name="msgtemp" value="/ <?=$mms_config[0]->lms_total_byte[0]?> Byte" size="6" style="border:0;" readonly oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
				</div>
				<div class="option">
					<div>
						<label><input type="checkbox" name="add_ad" id="add_ad" value="Y"> 광고성 문자 알림 추가</label>
						<div onclick="toggle_layer('warning_alarm');">
							<span class="icon_warning"></span>
							<div class="layer_view pink warning_alarm">
								<p>
									* 광고성정보 메시지는 21시~8시까지 발송이 제한됩니다.<br>
									(정보통신망법 제50조 제3항)
								</p>
								<dl>
									<dt>* 개정된 정보통신망법 제 50조 4항의 광고메시지 표기 의무사항 </dt>
									<dd>1) 메시지 시작부분에 반드시 (광고)로 표시</dd>
									<dd>2) 발신자 명칭, 업체명 또는 서비스명 표시</dd>
									<dd>3) 메시지 끝부분에는 반드시 무료수신거부번호 080-1234-5678 형식으로 표시</dd>
								</dl>
							</div>
						</div>
					</div>
					<div>
					<?if($cfg['use_080sms'] =='Y') {?>
						<label><input type="checkbox" name="add_080" id="add_080" value="Y"> 080 수신거부 메시지 추가</label>
					<?}else{?>
						<label onmouseenter="showToolTip(event,'080수신거부 설정 후 사용 가능합니다')" onmouseleave="hideToolTip();"><input type="checkbox" name="add_080" id="add_080" disabled value="Y"> 080 수신거부 메시지 추가</label>
					<?}?>
						<div>
							<a href="<?=$root_url?>/_manage/?body=member@receive_deny" target="_blank"><img src="<?=$engine_url?>/_manage/image/product/register/setup.png" class="btn_setup"></a>
						</div>
					</div>
				</div>
			</div>
			<div class="setup">
				<div id="sms_content">
					<div class="title">
						<strong>받는사람</strong> <span class="explain">여러명 발송시 엔터로 구분</span>
						<p><span id="senderCount" class="p_color3">0</span> 명</p>
					</div>
					<textarea name="rec_num" onBlur="countSmsRec(); mmsCheck(this.form);"><?=$cell?></textarea>
				</div>
				<div id="sms_phone">
					<div class="title">
						<strong>보내는 사람</strong>
						<p><a href="http://redirect.wisa.co.kr/mms_service2" target="_blank">발신번호 사전등록하기</a></p>
					</div>
					<?=selectArray($_mms_callback, "send_num", 1, "", numberOnly($call_back))?>
				</div>
				<div id="sms_res">
					<div class="title">
						<strong>예약발송</strong>
						<label class="p_cursor"><input type="checkbox" name="reserve" value="y"> 사용</label>
					</div>
					<select name="res_ym" style="float:left;">
						<?
							$sms_yy=date("Y",$now);
							$sms_mm=date("n",$now);
							$sms_dd=date("j",$now);
							$sms_hh=date("G",$now);

							for($ii=0; $ii<3; $ii++) {
								if($sms_mm<10) $mm="0".$sms_mm;
								else $mm=$sms_mm;
								echo("<option value=\"".$sms_yy.$mm."\">$sms_yy 년 $mm 월</option>");
								if($sms_mm==12) {
									$sms_yy++;
									$sms_mm=1;
								}
								else {
									$sms_mm++;
								}
							}
						?>
					</select>
					<select name="res_d" style="float:right;">
						<?
							for($ii=1; $ii<=31; $ii++) {
								if($ii==$sms_dd) $sel="selected";
								else $sel="";
								if($ii<10) $dd="0".$ii;
								else $dd=$ii;
								echo("<option value=\"$dd\" $sel>$dd 일</option>");
							}
						?>
					</select>
					<select name="res_h" style="float:left;">
						<?
							for($ii=0; $ii<24; $ii++) {
								if($ii==$sms_hh) $sel="selected";
								else $sel="";
								if($ii<10) $hh="0".$ii;
								else $hh=$ii;
								echo("<option value=\"$hh\" $sel>$hh 시</option>");
							}
						?>
					</select>
					<select name="res_i" style="float:right;">
						<?
							$ii=0;
							while($ii<60) {
								if($ii<10) $ss="0".$ii;
								else $ss=$ii;
								echo("<option value=\"$ss\" $sel>$ss 분</option>");
								$ii+=5;
							}
						?>
					</select>
				</div>
				<div id="sms_btn">
					<span class="box_btn blue"><input type="submit" value="보내기"></span>
					<span class="box_btn gray"><a href="javascript:;" onclick="window.close();">취소</a></span>
				</div>
			</div>
		</div>
	</div>
</form>
<form method="post" id="kakao_phone" name="kakao_phone" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return checkKakaoSMS(this)" target="hidden<?=$now?>" style="display:none;">
	<input type="hidden" name="body" value="<?=$_inc[0]?>@sms_sender.exe">
	<input type="hidden" name="sender" value="kko">
	<div class="tab_sms">
		<ul class="tab_sort">
			<li><a onclick="tab_sms_view(0)" class="p_cursor active">윙문자</a></li>
			<li class="active"><a onclick="tab_sms_view(1)" class="p_cursor">친구톡</a></li>
		</ul>
	</div>
	<div class="sms_sender sms_sender1 f_talk">
		<!-- 친구톡 SMS 인증 -->
		<div class="kakao_confirm">
			<h1>카카오 친구톡 스팸방지 조치로 인해 SMS인증이 필요합니다.</h1>
			<p class="msg">인증번호 받기 버튼을 클릭하세요.</p>
			<div class="box">
				<table>
					<colgroup>
						<col style="width:92px">
						<col>
					</colgroup>
					<tbody>
						<tr>
							<th>휴대폰번호</th>
							<td><strong><?=$admin_cell?></strong>
							<a href="./index.php?body=intra@my_info" target="_blank" class="edit">번호 변경</a>
							<?if($admin['cell']) {?>
							<span class="box_btn_s gray"><input type="button" value="인증번호 받기" onclick="sendKakaoFriendOTP('<?=$_mms_callback[0]?>', this);"></span></td>
							<?}?>
						</tr>
						<tr>
							<th>인증번호 입력</th>
							<td><input type="text" name="otpnum" class="input block"></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="btn">
				<span class="box_btn blue"><input type="button" value="확인" onclick="confirmKakaoFriendOTP();"></span>
				<span class="box_btn white"><input type="button" value="취소" onclick="self.close();"></span>
			</div>
		</div>
		<!-- //친구톡 SMS 인증 -->
		<p class="explain kakao_sendform">
			<span class="icon_info"></span> [서비스 및 차감포인트 안내] 텍스트형 22p / 이미지형 44p
		</p>
		<div class="info kakao_sendform">
			<table class="tbl_mini full">
				<caption class="hidden">친구톡 발송</caption>
				<colgroup>
					<col style="width:110px">
				</colgroup>
				<tr>
					<th scope="row">잔여 POINT</th>
					<td scope="row"><strong><?=number_format($total_point, 2)?></strong>&nbsp;<span class="box_btn_s"><input type="button" value="충전하기" onClick="goMywisa('?body=wing@sms_charge');"></span></td>
				</tr>
				<tr>
					<th scope="row">
						<div style="position:relative;">
							전송타입 <span class="info_square2 p_cursor" onclick="toggle_layer('layer_sendtype');">정보</span>
							<div class="layer_view layer_sendtype">
								<strong>전송타입</strong><br><br>이미지형 : 이미지 1개 + 400자 + 링크버튼 5개<br>텍스트형 : 1000자 + 링크버튼 5개
							</div>
						</div>
					</th>
					<td>친구톡</td>
				</tr>
				<tr>
					<th scope="row">
						<div style="position:relative;">
							이미지 선택 <span class="info_square2 p_cursor" onclick="toggle_layer('layer_img_select');">정보</span><p class="explain">(500 Kbyte)</p>
							<div class="layer_view layer_img_select">
								<strong>이미지형식</strong><br><br>권장 사이즈 : 720px*720px<br>제한 사이즈 : 가로 500px 미만 또는 가로:세로 비율이 2:1 미만 또는 3:4 초과시 업로드 불가<br>파일형식 및 크기 : jpg, png / 최대 500KB
							</div>
						</div>
					</th>
					<td>
						<p class="title first">파일선택</p>
						<input type="file" name="upfile" class="input block" onchange="mmsFileSend(this, 'kko');">
						<p class="title">파일목록</p>
						<label class="input block del_check kakao_file"> </label>
						<span class="box_btn_s full"><input type="button" value="삭제" onClick="kakaodeleteFile(this.form);"></span>
						<p class="title">이미지 URL</p>
						<input type="text" id="image_link" name="image_link" value="" class="input" size="14" placeholder="http://">
						<span class="box_btn_s"><input type="button" value="연결확인" onClick="link_check('image_link');"></span>
					</td>
				</tr>
				<tr>
					<th scope="row">링크버튼 <p class="explain">(5개까지 등록가능)</p></th>
					<td>
						<!--
						<select name="" class="block" onchange="type_view(value)">
							<option value="1">버튼 URL</option>
							<option value="2">어플리케이션주소</option>
							<option value="3">봇키워드</option>
							<option value="4">메세지전달</option>
						</select>
						-->
						<div class="btn_type type1">
							<p class="title">버튼명</p>
							<input type="text" name="button_name" value="" class="input block" maxlength="14">
							<p class="title">URL(PC)</p>
							<input type="text" id="button_purl" name="button_purl" value="" class="input" size="14" placeholder="http://">
							<span class="box_btn_s"><input type="button" value="연결확인" onClick="link_check('button_purl');"></span>
							<p class="title">URL(Mobile)</p>
							<input type="text" id="button_murl" name="button_murl" value="" class="input" size="14" placeholder="http://">
							<span class="box_btn_s"><input type="button" value="연결확인" onClick="link_check('button_murl');"></span>
						</div>
						<!--
						<div class="btn_type type2">
							<p class="title">버튼명</p>
							<input type="text" name="" value="" class="input block" maxlength="14">
							<p class="title">URL(IOS)</p>
							<input type="text" name="" value="" class="input block">
							<p class="title">URL(AOS)</p>
							<input type="text" name="" value="" class="input block">
						</div>
						<div class="btn_type type3">
							<p class="title">버튼명</p>
							<input type="text" name="" value="" class="input block" maxlength="14">
						</div>
						<div class="btn_type type4">
							<p class="title">버튼명</p>
							<input type="text" name="" value="" class="input block" maxlength="14">
						</div>
						-->
						<span class="box_btn_s gray full apply"><input type="button" value="적용" onClick="button_submit(this.form);"></span>
					</td>
				</tr>
			</table>
		</div>
		<div id="sms_phone_body" class="kakao_sendform">
			<div class="phone">
				<div id="sms_msg">
					<div id="kakao_img" class="img"></div>
					<textarea name="msg" id="kakao_msg" onKeyUp="kakaocheckByte(this.form);" maxlength="1000"><?=stripslashes($msg)?></textarea>
					<ul id="list_btn"class="list_btn"></ul>
				</div>
				<div class="byte">
					<input name="msglen" type="text" value="0" size="3" style="border:0; color:red;" dir="rtl" readonly oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
					<input type="text" id="kakaomsgtemp" name="msgtemp" value="/ 1000 자" size="6" style="border:0;" readonly oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
				</div>
			</div>
			<div class="setup">
				<div id="sms_content">
					<div class="title">
						<strong>받는사람</strong> <span class="explain">여러명 발송시 엔터로 구분</span>
						<p><span id="KakaosenderCount" class="p_color3">0</span> 명</p>
					</div>
					<textarea name="kakao_rec_num" onBlur="kakaocountRec();"><?=$cell?></textarea>
				</div>
				<div id="sms_phone">
					<div class="title">
						<strong>보내는 사람</strong>
					</div>
					<input type="text" value="<?=$cfg['alimtalk_id']?>" class="input block" readonly>
				</div>
				<div id="sms_res">
					<div class="title">
						<strong>예약발송</strong>
						<label class="p_cursor"><input type="checkbox" name="reserve" value="y"> 사용</label>
					</div>
					<select name="res_ym" style="float:left;">
						<?
							$sms_yy=date("Y",$now);
							$sms_mm=date("n",$now);
							$sms_dd=date("j",$now);
							$sms_hh=date("G",$now);

							for($ii=0; $ii<3; $ii++) {
								if($sms_mm<10) $mm="0".$sms_mm;
								else $mm=$sms_mm;
								echo("<option value=\"".$sms_yy.$mm."\">$sms_yy 년 $mm 월</option>");
								if($sms_mm==12) {
									$sms_yy++;
									$sms_mm=1;
								}
								else {
									$sms_mm++;
								}
							}
						?>
					</select>
					<select name="res_d" style="float:right;">
						<?
							for($ii=1; $ii<=31; $ii++) {
								if($ii==$sms_dd) $sel="selected";
								else $sel="";
								if($ii<10) $dd="0".$ii;
								else $dd=$ii;
								echo("<option value=\"$dd\" $sel>$dd 일</option>");
							}
						?>
					</select>
					<select name="res_h" style="float:left;">
						<?
							for($ii=8; $ii<20; $ii++) {
								if($ii==$sms_hh) $sel="selected";
								else $sel="";
								if($ii<10) $hh="0".$ii;
								else $hh=$ii;
								echo("<option value=\"$hh\" $sel>$hh 시</option>");
							}
						?>
					</select>
					<select name="res_i" style="float:right;">
						<?
							$ii=0;
							while($ii<60) {
								if($ii<10) $ss="0".$ii;
								else $ss=$ii;
								echo("<option value=\"$ss\" $sel>$ss 분</option>");
								$ii+=5;
							}
						?>
					</select>
				</div>
				<div id="sms_btn">
					<span class="box_btn full test"><a onclick="popup_test_check();">테스트발송</a></span>
					<span class="box_btn blue full"><input type="submit" value="보내기"></span>
					<span class="box_btn gray full"><a href="javascript:;" onclick="window.close();">취소</a></span>
				</div>
				<ul class="list_info tp">
					<li><span class="warning">채널과 친구인 회원에게만 발송</span>됩니다.</li>
					<li>채널과 친구인 회원은 카카오 정책 상 확인이 불가능하며, 카카오톡 채널 관리자센터를 통해 전체 친구 수를 확인할 수 있습니다. </li>
					<li><span class="warning">야간(20:00 ~ 익일 8:00) 발송이 제한</span>됩니다.</li>
				</ul>
			</div>
		</div>
		<div class="popup_test">
			<div class="popupContent">
				<div id="header" class="popup_hd_line">
					<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
					<div id="mngTab_pop">친구톡 테스트 발송</div>
				</div>
				<div class="box">
					<ul class="list_info">
						<li class="explain">친구톡 테스트 발송은 휴대폰 정보가 등록된 관리자에 한해 발송이 가능하며, 플러스 친구와 친구인 경우에만 정상 발송됩니다.</li>
						<li class="explain">관리자 정보 수정은 인트라넷 > 인트라넷관리 > 사원 등록/관리를 통해 수정이 가능합니다. <a href="/_manage/?body=intra@staffs_edt" target="_blank">바로가기</a></li>
					</ul>
					<div class="frame">
						<div class="select">
							<ul>
								<?php
								$cnt = 0;
								$res = $pdo->iterator("select * from `$tbl[mng]` where cell!=''");
                                foreach ($res as $data) {
								?>
									<li data-no="<?=$data['no']?>" data-cell="<?=$data['cell']?>"><input type="checkbox" name="" value="" id="ft_test<?=$data['no']?>" class="check_img"><label for="ft_test<?=$data['no']?>"><?=$data['name']?>(<?=$data['admin_id']?>)</label></li>
								<?
								$cnt++;
								}
								?>
							</ul>
						</div>
						<div class="list">
							<ul>
							</ul>
						</div>
					</div>
					<div class="btn">
						<span class="box_btn blue"><input type="button" onClick="popup_test_submit();" value="보내기"></span>
						<span class="box_btn"><a onclick="$('.popup_test').hide()">닫기</a></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
	window.onload=function (){
		checkByte(document.phone, 1);
		countSmsRec();
		kakaocountRec();
		document.phone.msg.focus();
	}

	$('#add_ad').change(function() {
		if (this.checked === true) {
			this.form.msg.value = "(광고)(<?=$cfg['company_mall_name']?>)" + this.form.msg.value;
		} else {
			this.form.msg.value = this.form.msg.value.replace("(광고)(<?=$cfg['company_mall_name']?>)", '');
		}
	});

	$('#add_080').change(function() {
		if (this.checked === true) {
			this.form.msg.value = this.form.msg.value + "\n무료수신거부번호 : <?=$cfg['080_number']?>";
		} else {
			this.form.msg.value = this.form.msg.value.replace("\n무료수신거부번호 : <?=$cfg['080_number']?>", "");
		}
	});

	function mmsFileSend(o, mode) {
		var kakao_phone = document.getElementById('kakao_phone');
		if(kakao_phone.style.display == 'block') {
			type = 'kakao';
		}else {
			type = 'wing';
		}

		if(type=='wing') {
			if($('.file_list>option').length >= 3) {
				window.alert('MMS 이미지는 최대 3개까지만 업로드 가능합니다.');
				return false;
			}
		}

		var reader = new FileReader();
		reader.onload = function(e) {
            var fd = new FormData();
            fd.append('account_idx', '<?=$mms_config[0]->new_folder[0]?>');
            fd.append('bin', e.target.result);
            fd.append('mode', (mode) ? mode : '');

            $.ajax({
                'url': '?body=extension@sms_attach.exe',
                'type':'post',
                'contentType': false,
                'processData': false,
                'async': false,
                'data': fd,
                'success': function(r) {
                    try {
                        if (r.result == 'success') {
                            for (var i = 0; i < r.datas.length; i++) {
                                appendFile(r.datas[i].path, o.files[0].name);
                            }
                        } else if (r.result == 'faild') {
                            window.alert(r.message);
                        } else {
                            window.alert('업로드중 오류가 발생하였습니다.');
                        }
                    } catch(e) {
                        window.alert('업로드중 오류가 발생하였습니다.');
                    }
                },
            });
			o.value = '';
		}
		reader.readAsDataURL(o.files[0]);
	}

	function appendFile(path, fname) {
		var kakao_phone = document.getElementById('kakao_phone');
		if(kakao_phone.style.display == 'block') {
			type = 'kakao';
		}else {
			type = 'wing';
		}

		if(type=='wing') {
			var o = $("<option value='"+path+"'>"+fname+"</option>");
			$('.file_list').append(o);
			filePreview(o[0]);
			reloadFile();
		}else {
			$('.kakao_file').html("<input type='hidden' id='delfile' name='delfile' value='"+path+"'>"+fname);
			kakaofilePreview(path);
		}
	}

	function filePreview(s){
		if(s.value) {
		var src = "<?=$mms_config[0]->uploader[0]?>?exec=preview&account_idx=<?=$mms_config[0]->new_folder[0]?>&filename="+s.value;
			$('#file_preview').html("<img src='"+src+"' style='max-width:100%; max-height:100%'>");
			$('#sms_type').html('MMS');
		}
	}

	function deleteUploadFile() {
		var o =	$('.file_list').find(':selected');
		$.ajax({
			'url': '<?=$mms_config[0]->uploader[0]?>',
			'dataType': 'jsonp',
			'data': {'exec':'remove', 'account_idx':'<?=$mms_config[0]->new_folder[0]?>', 'filename':o.val()},
			'jsonpCallback': 'callback',
			'success': function(r) {
				$('#file_preview').html('');
				o.remove();
				reloadFile();
			}
		});
		if($('.file_list').find('option').size()<=1) {
			$('#sms_type').html('SMS');
		}
	}

	function reloadFile() {
		var tmp = "";
		$('.file_list>option').each(function() {
			if(tmp) tmp += "@";
			tmp += this.value.replace(/^[0-9]+\//, '');
		});
		$("[name=file_list]").val(tmp);
	}

	function kakaofilePreview(s){
		var src = "<?=$mms_config[0]->uploader[0]?>?exec=preview&account_idx=<?=$mms_config[0]->new_folder[0]?>&filename="+s;
		$('#kakao_img').html("<img src='"+src+"'>");
		$('#kakaomsgtemp').val(' / 400 자');
		$('#kakao_msg').attr('maxlength', '400');
		kakaocheckByte(document.kakao_phone);
	}

	function kakaodeleteFile() {
		var o =	$('#delfile').val();
		$.ajax({
			'url': '<?=$mms_config[0]->uploader[0]?>',
			'dataType': 'jsonp',
			'data': {'exec':'remove', 'account_idx':'<?=$mms_config[0]->new_folder[0]?>', 'filename':o},
			'jsonpCallback': 'callback',
			'success': function(r) {
				$('#kakao_img').html('');
				$('.kakao_file').html('');
				$('#kakaomsgtemp').val(' / 1000 자');
				$('#kakao_msg').attr('maxlength', '1000');
				kakaocheckByte(document.kakao_phone);
			}
		});
	}

	function toggle_layer(name) {
		$('.'+name).not('.'+name).hide();
		$('.'+name).toggle();
	}

	function type_view(no) {
		$('.btn_type').hide();
		$('.btn_type.type'+no).show();
	}

	function tab_sms_view(no) {
		var kakao_phone = document.getElementById('kakao_phone');
		var alimtalk_id = '<?=$cfg[alimtalk_id]?>';
		var alimtalk_key = '<?=$cfg[alimtalk_profile_key]?>';
		if(kakao_phone.style.display == 'block') {
			type = 1;
		}else {
			type = 0;
		}
		if(no==0) {
			var mw = 0;
			var mh = -90;
		}else {
			if(!alimtalk_id && !alimtalk_key) {
				if(confirm("카카오 알림톡 신청 후 이용하실 수 있습니다.\n 해당 페이지로 이동하시겠습니까?")) {
					goMywisa('?body=wing@service@alimtalk');
					return false;
				}else {
					return false;
				}
			}
			var mw = 0;
			var mh = 90;
		}
		if(no!=type) {
			window.resizeBy(mw, mh);
		}
		var tabs = $('.tab_sort').find('li');
		tabs.each(function(idx) {
			var fphone = $('#phone');
			var fkakaophone = $('#kakao_phone');
			if(no==0) {
				fphone.css('display', 'block');
				fkakaophone.css('display', 'none');
			}else {
				fphone.css('display', 'none');
				fkakaophone.css('display', 'block');
			}
		})
	}

	function button_submit(f) {
		var button_name = f.button_name.value;
		var button_purl = f.button_purl.value;
		var button_murl = f.button_murl.value;
		var li_count = $('#sms_msg .list_btn li').length+1;

		if(!button_name) {
			alert("버튼명이 입력되지 않았습니다.");
			return false;
		}
		if(!button_purl) {
			alert("URL(PC)가 입력되지 않았습니다.");
			return false;
		}
		if(!button_murl) {
			alert("URL(Mobile)이 입력되지 않았습니다.");
			return false;
		}
		if(li_count<=5) {
			var chk_index = 0;
			$('#sms_msg .list_btn li').each(function(i) {
				var now_index = $(this).data('index');
				if(chk_index==0 || now_index >= chk_index) {
					chk_index = now_index;
				}
			})
			chk_index = chk_index + 1;
			$('#sms_msg .list_btn').append("<li data-index='"+chk_index+"'>"+button_name+"<a data-purl='"+button_purl+"' data-murl='"+button_murl+"' class='del' onClick='button_del("+chk_index+")'></a><input type='hidden' name='button_name[]' value='"+button_name+"'><input type='hidden' name='button_type[]' value='WL'><input type='hidden' name='button_purl[]' value='"+button_purl+"'><input type='hidden' name='button_murl[]' value='"+button_murl+"'></li>");
			f.button_name.value = "";
			f.button_purl.value = "";
			f.button_murl.value = "";
		}else {
			alert("링크버튼은 최대 5개까지 등록할 수 있습니다.");
			return false;
		}
	}

	function button_del(idx) {
		$('#sms_msg .list_btn li').each(function() {
			var now_index = $(this).data('index');
			if(idx==now_index) {
				$(this).remove();
			}
		})
	}

	function kakaocheckByte(f) {
		var kakao_msg = $('#kakao_msg').val();

		if($('#kakao_img img').size()>0) {
			if(kakao_msg.length>400) {
				alert("400자 이하로 작성해주세요.");
				$('#kakao_msg').val(kakao_msg.substring(0, 400));
				var kakao_msg = $('#kakao_msg').val();
			}
		}else {
			if(kakao_msg.length>1000) {
				alert("1000자 이하로 작성해주세요.");
				$('#kakao_msg').val(kakao_msg.substring(0, 1000));
				var kakao_msg = $('#kakao_msg').val();
			}
		}
		f.msglen.value = kakao_msg.length;
	}

	$("#list_btn").sortable({
		'placeholder': 'placeholder',
		'cursor':'all-scroll',
		'scroll': false
	});

	$('#image_link').click(function() {
		$(this).val('http://');
	});
	$('#button_purl').click(function() {
		$(this).val('http://');
	});
	$('#button_murl').click(function() {
		$(this).val('http://');
	});

	function link_check(href) {
		var url = $('#'+href).val();
		window.open(url);
	}

	$('.popup_test .frame .select ul li').on('click', function(event) {
		var pass = "";
		event.preventDefault();

		no = $(this).data('no');
		cell = $(this).data('cell');
		ft_select = $('label[for=ft_test'+no+']').text();

		if($(this).find('label').hasClass("on")) {
			$(this).find('label').removeClass("on");
			$('.popup_test .list ul li').each(function() {
				no2 = $(this).data('no');
				if(no==no2) {
					$(this).remove();
				}
			});
		}else{
			$(this).find('label').addClass("on");
			if(pass!="Y") {
				$('.popup_test .list ul').append("<li data-no='"+no+"' data-cell='"+cell+"'>"+ft_select+"<a onClick='removeMngCell("+no+")'>삭제</a></li>");
			}
		}
	});

	function removeMngCell(no) {
		$('.popup_test .list ul li').each(function() {
			no2 = $(this).data('no');
			if(no==no2) {
				$(this).remove();
			}
		});
		$('.popup_test .select ul li').each(function() {
			no2 = $(this).data('no');
			if(no==no2) {
				$(this).find('label').removeClass("on");
			}
		});
	}

	function popup_test_check() {
		var f = document.kakao_phone;

		if(!f.kakao_msg.value && !$('#delfile').val() && $('#sms_msg .list_btn li').length==0) {
			alert("테스트발송에 필요한 친구톡 메시지가 입력되지 않았습니다.");
		}else {
			$('.popup_test').show();
		}
	}

	function popup_test_submit() {
        var fdata = $("form[name=kakao_phone]").serialize();
		var kakao_rec_num = "";
		if($('.popup_test .list ul li').length==0) {
			alert("관리자를 선택해주세요.");
			return false;
		}
		$('.popup_test .list ul li').each(function() {
			cell = $(this).data('cell');
			kakao_rec_num += '\n'+cell;
		});
        $.ajax({
            type : 'post',
            url : '?body=extension@sms_sender.exe',
            data : fdata+'&kakao_rec_num='+kakao_rec_num+'&sender_type=test',
            dataType : 'html',
            success : function(){
				$('.popup_test').hide();
                alert("테스트 발송이 완료 되었습니다.");
            },
        });
	}

	function sendKakaoFriendOTP(sender, o) {
		$(o).hide();
		$.post('./index.php', {'body':'extension@kakao_otp.exe', 'exec':'send', 'sender':sender}, function(r) {
			window.alert(r.message);
			$(o).show();
		});
	}

	function confirmKakaoFriendOTP() {
		var otpnum = $('input[name=otpnum]').val();
		if(otpnum == '') {
			window.alert('인증번호를 입력해주세요.');
			return false;
		}
		$.post('./index.php', {'body':'extension@kakao_otp.exe', 'exec':'confirm', 'otpnum':otpnum}, function(r) {
			if(r.result == 'OK') {
				$('.kakao_confirm').remove();
				$('.kakao_sendform').show();
			} else {
				window.alert(r.message);
			}
		});
	}

	<?if($_SESSION['kakao_friend_otp'] == true) {?>
	$('.kakao_confirm').remove();
	$('.kakao_sendform').show();
	<?}?>
</script>