<?PHP

	header('Content-type:application/json; charset='._BASE_CHARSET_);

	if($_POST['exec'] == 'send') {
		$sender = $_POST['sender'];
		$otp = sprintf('%04d', rand(0,9999));
		$msg = sprintf("[%s] 친구톡 발송 인증번호는 '%s' 입니다.", $cfg['company_mall_name'], $otp);
		if(defined('__WEAGLEEYE_OUTSIDE__') == false) {
			$msg = iconv(_BASE_CHARSET_, "EUC-KR", $msg);
		}

		if(empty($admin['cell']) == true) {
			exit(json_encode(array(
				'result' => 'error',
				'message' => '관리자 휴대폰 번호가 설정되어 있지 않습니다.'
			)));
		}

		if(empty($sender) == true) {
			exit(json_encode(array(
				'result' => 'error',
				'message' => '보내는 사람 연락처가 설정되어 있지 않습니다.'
			)));
		}

		$mms = new WeagleEyeClient($_we, $cfg['sms_module']);

		// sms 건당 필요 포인트
		$mms_config = $mms->call('getMmsConfig');
		$use_point = explode('/', $mms_config[0]->use_point[0]);

		// 보유 포인트
		$total_point = $mms->call('mms_get_rest', array(
			'args1' => 'wing',
			'args2' => $wec->config['account_idx']
		));

		// 잔여 포인트 체크
		if($total_point < $use_point[1]) {
			exit(json_encode(array(
				'result' => 'error',
				'message' => '잔여 윙문자 포인트가 부족합니다.'
			)));
		}

		// 문자 발송
		$mms->queue('mms_send', 'wing', $wec->config['account_idx'], $admin['cell'], 1, $sender, $msg, $msg);
		$mms->send_clean();

		if($mms->result == 'OK') {
			$_SESSION['otp_tmp'] = $otp;
			exit(json_encode(array(
				'result' => 'success',
				'message' => '인증문자가 발송되었습니다.'
			)));
		} else {
			exit(json_encode(array(
				'result' => 'error',
				'message' => $mms->result
			)));
		}
	} else if($_POST['exec'] == 'confirm') {
		$otpnum = trim($_POST['otpnum']);

		if(isset($_SESSION['otp_tmp']) == false) {
			exit(json_encode(array(
				'result' => 'error',
				'message' => '인증번호를 발송해주세요.',
			)));
		}

		if(strcmp($_SESSION['otp_tmp'], $otpnum) !== 0) {
			exit(json_encode(array(
				'result' => 'error',
				'message' => '인증번호가 일치하지 않습니다.'.$_SESSION['otp_tmp'],
			)));
		}

		$_SESSION['kakao_friend_otp'] = true;
		unset($_SESSION['otp_tmp']);

		exit(json_encode(array(
			'result' => 'OK',
			'message' => '인증되었습니다.',
		)));
	}

?>