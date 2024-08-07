<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$e = addslashes($_REQUEST['e']);
	$m = $pdo->assoc("select no, mailing, cell, email, name from $tbl[member] where md5(email)='$e'");

	if(!$m['no']) exit('e:정상적인 회원정보가 아닙니다.');
	if($m['mailing'] != 'Y') exit('e:이미 수신거부 되었습니다.');

	$pdo->query("update $tbl[member] set mailing='N' where no='$m[no]'");

	if($cfg['use_edit_receive']=="Y") {
		if($cfg['edit_receive_type']=='sms') {
			$sms_replace['agree_date'] = date("Y/m/d", $now);
			$sms_replace['agree_receive'] = sprintf(__lang_agree_sms_email_yn__, __lang_agree_email_optout__);
			include $engine_dir."/_engine/sms/sms_module.php";
			$r = SMS_send_case(23, $m['cell']);
		}else {
			$mail_case = 21;
			$marketing_regdate = date("Y/m/d", $now);
			$sms_email_yn = sprintf(__lang_agree_sms_email_yn__, __lang_agree_email_optout__);
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$r = sendMailContent($mail_case, $m['name'], $m['email']);
		}
	}
	exit('ok');

?>