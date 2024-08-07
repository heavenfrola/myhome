<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  문자 발송 처리
	' +----------------------------------------------------------------------------------------------+*/

    set_time_limit(0);

	include_once $engine_dir."/_engine/sms/sms_module.php";
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	$mms_config = $we_mms->call('getMmsConfig');
	$mms_use_point = explode('/', $mms_config[0]->use_point[0]);

	$reserve = $_POST['reserve'];
	if ($reserve == "y") { // 예약발송
		$res_ym = numberOnly($_POST['res_ym']);
		$res_d = numberOnly($_POST['res_d']);
		$res_h = numberOnly($_POST['res_h']);
		$res_i = numberOnly($_POST['res_i']);
		$s_date = $res_ym.$res_d." ".$res_h.$res_i;
		$s_date = date("Y-m-d H:i",strtotime($s_date)).":00";
	} else {
		$s_date= date("Y-m-d H:i", $now).":00";
	}

	$layer1 = 'smsBlind';
	$layer2 = 'smsING';

	// MMS 잔여포인트 조회
	$we_mms = new WeagleEyeClient($_we, $cfg['sms_module']);
	$we_mms->queue("mms_get_rest", "wing", $wec->config['account_idx']);
	$we_mms->send_clean();
	$total_point=numberOnly($we_mms->result);

    // 한글코드 체크
    if ($sender != 'kko') {
        $euckr = iconv(_BASE_CHARSET_, "EUC-KR//IGNORE", $_POST['msg']);
        $check = iconv('EUC-KR', _BASE_CHARSET_, $euckr);
        if ($_POST['msg'] != $check) {
            msg("문자내용에 통신사에서 지원지않는 문자셋이 포함되어있습니다.\\n특수문자나 오타를 점검해주시기 바랍니다.");
        }
    }

	$sender = $_POST['sender'];
	$msg = preg_replace('/\r/', '', $_POST['msg']);
    $msg_euckr = iconv(_BASE_CHARSET_, "EUC-KR//IGNORE", $msg);
	if($sender != 'kko' && defined('__WEAGLEEYE_OUTSIDE__') == false) {
		$msg = $msg_euckr;
	}

	if($sender == 'kko' && isset($_SESSION['kakao_friend_otp']) == false) {
		msg('친구톡 사용 인증을 해주세요.');
	}

	$msg_type=1;
	$msg_byte=strlen($msg_euckr);
	if($msg_byte > $mms_config[0]->lms_total_byte[0]) msg("글자길이는 ".number_format($mms_config[0]->lms_total_byte[0])."byte를 초과할 수 없으므로 확인 후 다시 시도해주시기 바랍니다"); // 최대길이수 제어
	if($msg_byte > $mms_config[0]->sms_total_byte[0]){
		$msg_type=2; // LMS
	}
	if (strlen(trim($_POST['file_list'])) > 0) {
		$msg_type=3; // MMS
	}
	if($sender == 'kko') $msg_type = 5;
	$msg_point=$mms_use_point[$msg_type]; // 개당 발송 포인트

	if($total_point < $mms_use_point[$msg_type]){
		msg("잔여 포인트가 부족하므로 충전하신 뒤 이용해주시기 바랍니다");
	}

	function array_trim(&$array){
		$array=trim($array);
	}
	# sms 발송 수량
	if($sender == 'kko') {
		$_POST['rec_num'] = $_POST['kakao_rec_num'];
	}
	$receiver = explode("\n",$_POST['rec_num']); // 전화번호 개수
	array_walk($receiver, "array_trim"); // 값 trim
	$receiver = array_unique($receiver); // 중복번호 제거
	$total_receiver = count($receiver);
	$receiver=@implode("@", $receiver);

	if($total_receiver < 1) msg("전송번호가 존재하지 않습니다. 받는 사람의 번호를 확인해주시기 바랍니다");

	$msg_total_point=$msg_point*$total_receiver; // 개당 발송 포인트
	if($total_point < $msg_total_point) msg("\\n현재 보유하고 계신 포인트가 부족합니다\\n\\n전송할 대상을 줄이시거나 충전해주시기 바랍니다                    \\n\\n- 총사용 POINT : ".number_format($msg_total_point).", 보유 POINT : ".number_format($total_point).", 부족 POINT : ".number_format($msg_total_point-$total_point));

	// mms 발송
	$send_num = $_POST['send_num'];
	$subject = cutStr($msg, 20); // mms 제목
	$files = addslashes($_POST['file_list']);

	$case="10";
	if($sender == 'kko') {
		if($_POST['delfile'] && !$_POST['image_link']) {
			msg('친구톡 이미지 업로드시 이미지 URL을 반드시 설정하셔야 합니다.');
		}

		// 버튼 생성
		if(is_array($_POST['button_name'])) {
			foreach($_POST['button_name'] as $key => $val) {
				$btn_no = ($key+1);
				${'button'.$btn_no} = urlencode(json_encode(array(
					'name' => $val,
					'type' => $_POST['button_type'][$key],
					'url_pc' => $_POST['button_purl'][$key],
					'url_mobile' => $_POST['button_murl'][$key],
				)));
			}
		}
		$reserved_time = ($reserve == 'y') ? date('YmdHis', strtotime($s_date)):'00000000000000';
		$wec_alm = new weagleEyeClient($GLOBALS['_we'], 'alimtalk');
		$wec_alm->call('alimtalk', array(
			'send_case' => $case,
			'profile_key' => $cfg['alimtalk_profile_key'],
			'message_type' => 'ft',
			'template_code' => $data['alimtalk_code'],
			'send_num' => $send_num,
			'receiver_num' => $receiver,
			'message' => urlencode($msg),
			'reserved_time' => $reserved_time,
			'root_url' => $root_url,
			'image' => $_POST['delfile'],
			'image_link' => $_POST['image_link'],
			'button1' => $button1,
			'button2' => $button2,
			'button3' => $button3,
			'button4' => $button4,
			'button5' => $button5
		));
		$result = json_decode($wec_alm->result);
		if(is_object($result) == false) {
			$wec_alm->result = iconv('euckr', 'utf8', $wec_alm->result);
			alert(php2java($wec_alm->result));
			exit;
		}
	} else {
		$we_mms->queue("mms_send", "wing", $wec->config['account_idx'], $receiver, $total_receiver, $send_num, $subject, $msg, $files, $root_url, $s_date, $case);
		$we_mms->send_clean();

		if(strpos($we_mms->result, "ERROR") > -1) msg("전송 중 에러가 발생되어 발송이 중지되었습니다");
	}

	// 파일 목록 삭제
	unset($_SESSION[mms_upfile_name]);

	if($_POST['sender_type']!="test") {
?>
<script language="JavaScript">
	window.alert('\n 총 <?=$total_receiver?> 명에게 메세지를 전송하였습니다\n');
	parent.window.location.href='./?body=<?=$_inc[0]?>@sms_sender.frm';
</script>
<?	}
exit;?>