<?php

/**
 * okname 인증 요청
 */

require_once __ENGINE_DIR__ . '/_engine/include/common.lib.php';
require_once 'lib.php';

// 변수 세팅
$SITE_NAME = $scfg->get('company_mall_name');
$SITE_URL = preg_replace('@https?://@', '', $root_url);
$CP_CD = $scfg->get('kcb_cpcd');
$RETURN_URL = $root_url.'/main/exec.php?exec_file=member/kcb/receive.php';
$RQST_CAUS_CD = '00'; // 인증 요청 사유코드
$target = 'PROD';	// 테스트="TEST", 운영="PROD"
$popupUrl = "https://safe.ok-name.co.kr/CommonSvl";	// 운영 URL
$license = $root_dir . '/_data/okname/license.dat';

$params = json_encode([
	'CP_CD' => $CP_CD,
	'RETURN_URL' => $RETURN_URL,
	'SITE_NAME' => $SITE_NAME,
	'SITE_URL' => $SITE_URL,
	'RQST_CAUS_CD' => $RQST_CAUS_CD
]);

// 성공 후 이동할 경로
$_SESSION['okname_rurl'] = ($_POST['rurl']) ? $_POST['rurl'] : $root_url;

// okcert3 실행
$svcName = "IDS_HS_POPUP_START";
$out = NULL;
$ret = okcert3_u($target, $CP_CD, $svcName, $params, $license, $out);

// 실행 결과
$RSLT_CD = ''; // 결과코드
$RSLT_MSG = ''; // 결과메시지
$MDL_TKN = ''; // 모듈토큰
$TX_SEQ_NO = ''; // 거래일련번호

if ($ret == 0) {
	$output = json_decode($out, true);

	$RSLT_CD = $output['RSLT_CD'];
	$RSLT_MSG = $output['RSLT_MSG'];

	if (isset($output['TX_SEQ_NO'])) $TX_SEQ_NO = $output['TX_SEQ_NO']; // 필요 시 거래 일련 번호 에 대하여 DB저장 등의 처리

	if($RSLT_CD == 'B000') { // B000 : 정상건
		$MDL_TKN = $output['MDL_TKN'];
	}
} else {
	msg('인증 실패', 'close');
}

?>
<title>휴대폰 본인확인</title>
<script>
	function request(){
		document.form1.action = "<?=$popupUrl?>";
		document.form1.method = "post";

		document.form1.submit();
	}
</script>
</head>

<body>
	<form name="form1">
		<input type="hidden" name="tc" value="kcb.oknm.online.safehscert.popup.cmd.P931_CertChoiceCmd">
		<input type="hidden" name="cp_cd" value="<?=$CP_CD?>">
		<input type="hidden" name="mdl_tkn" value="<?=$MDL_TKN?>">
		<input type="hidden" name="target_id" value="">
	</form>
<?php

if ($RSLT_CD == "B000") { //인증요청
	echo ("<script>request();</script>");
} else { //요청 실패
	msg("$RSLT_CD : $RSLT_MSG", 'close');
}

?>
</body>
</html>
