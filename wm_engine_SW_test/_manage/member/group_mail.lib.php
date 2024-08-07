<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  단체메일 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	$we_mail = new weagleEyeClient($_we, 'groupMail');

	function mailLimitCk($action = null) {
		global $we_mail;

		# 단체메일 계정정보
		$account = $we_mail->get('410', '', true);
		$account = $account[0];

		if($_POST['mtype'] == 2) {

			$month_limit = 0;
			$mail_rest = $account->premium_mail_rest[0];
			$m_total = 0;
			$total = $mail_rest;
		} else {

			$mail_rest = $account->mail_rest[0]; // 충전갯수
			$month_limit = $account->email_limit[0]; // 월간 기본 제공수

			# 월간 사용메일 수
			$mail_send = $we_mail->get('420', '', true);
			$m_total = $mail_send[0]->total1[0] + $mail_send[0]->total2[0];

			if($mail_rest < 1) $mail_rest = 0;
			$total = ($month_limit - $m_total < 1) ? 0 : $month_limit - $m_total;
			$total += $mail_rest;
		}

		if($total < 1 && $GLOBALS['body'] != 'main@main') msg('최대 전송 건수를 초과하였습니다. 고객센터에서 충전후 사용하여 주시기 바랍니다', $action);
		return array($month_limit, $mail_rest, $m_total, $total);
	}

?>