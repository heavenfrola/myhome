<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  080sms 수신거부
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/member.lib.php';

	printAjaxHeader();

	if($cfg['use_080sms'] == 'Y') {
		if($cfg['080_access_ip'] != $_SERVER['REMOTE_ADDR']) exit('정상적인 접속이 아닙니다.');
		if(str_replace('-', '', $cfg['080_number']) != str_replace('-', '', $_POST['comid'])) exit('080대표번호가 다릅니다.');
		$cell = str_replace('-', '', $_POST['deniedphoneno']);
		$mem = $pdo->assoc("select email, name, sms, mailing, no from $tbl[member] where replace(cell, '-', '') = $cell");

		$pdo->query("update $tbl[member] set sms = 'N' where replace(cell, '-', '') = $cell");

		setAdvInfoDate($mem['no'], $mem['mailing'], $mem['sms']);

		if($cfg['use_edit_receive']=="Y") {
			if($cfg['edit_receive_type']=='sms') {
				$sms_replace['agree_date'] = date("Y/m/d", $now);
				$sms_replace['agree_receive'] = sprintf(__lang_agree_sms_email_yn__, __lang_agree_sms_optout__);
				include $engine_dir."/_engine/sms/sms_module.php";
				$r = SMS_send_case(23, $cell);
			}else {
				$mail_case = 21;
				$marketing_regdate = date("Y/m/d", $now);
				$sms_email_yn = sprintf(__lang_agree_sms_email_yn__, __lang_agree_sms_optout__);
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				$r = sendMailContent($mail_case, $mem['name'], $mem['email']);
			}
		}
		exit('RCV_OK');
	} else {
		exit('080 수신거부를 사용중이 아닙니다.');
	}
?>