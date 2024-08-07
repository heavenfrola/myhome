<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $sitecode = $cfg['ipin_checkplus_id'];
    $sitepasswd = $cfg['ipin_checkplus_pw'];

    $cb_encode_path = $engine_dir.'/_engine/member/ipinCheckPlus/bin/CPClient';
    $authtype = '';

	$popgubun 	= 'N';	//Y : 취소버튼 있음 / N : 취소버튼 없음
	$customize 	= $_SESSION['browser_type'] == 'mobile' ? 'Mobile' : '';

	$reqseq = `$cb_encode_path SEQ $sitecode`;

    $returnurl = $root_url.'/main/exec.php?exec_file=member/ipinCheckPlus/success.exe.php';
    $errorurl = $root_url.'/main/exec.php?exec_file=member/ipinCheckPlus/fail.exe.php';

    $_SESSION["REQ_SEQ"] = $reqseq;

    $plaindata = "7:REQ_SEQ".strlen($reqseq).":".$reqseq.
				"8:SITECODE".strlen($sitecode).":".$sitecode.
				"9:AUTH_TYPE".strlen($authtype).":". $authtype.
				"7:RTN_URL".strlen($returnurl).":".$returnurl.
				"7:ERR_URL".strlen($errorurl).":".$errorurl.
				"11:POPUP_GUBUN".strlen($popgubun).":".$popgubun.
				"9:CUSTOMIZE".strlen($customize).":".$customize;

    $enc_data = `$cb_encode_path ENC $sitecode $sitepasswd $plaindata`;

	switch($enc_data) {
		case -1 :
			$returnMsg = '암/복호화 시스템 오류입니다.';
			$enc_data = '';
		break;
		case -2 :
			$returnMsg = '암호화 처리 오류입니다.';
			$enc_data = '';
		break;
		case -3 :
			$returnMsg = '암호화 데이터 오류 입니다.';
			$enc_data = '';
		break;
		case -9 :
			$returnMsg = '입력값 오류 입니다.';
			$enc_data = '';
		break;
    }

?>
<form name="form_chk" method="post" target="_self" action="https://nice.checkplus.co.kr/CheckPlusSafeModel/checkplus.cb">
    <input type="hidden" name="m" value="checkplusSerivce">
    <input type="hidden" name="EncodeData" value="<?= $enc_data ?>">
</form>
<script type='text/javascript'>
    document.form_chk.submit();
</script>