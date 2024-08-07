<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $sSiteCode = $cfg['ipin_id'];
    $sSitePw = $cfg['ipin_pw'];
	$sModulePath = $engine_dir.'/_engine/member/ipin/bin/IPINClient';;
	$sReturnURL = $root_url.'/main/exec.php?exec_file=member/ipin/process.exe.php';
	$sCPRequest = "";

	$sCPRequest = `$sModulePath SEQ $sSiteCode`;
	$_SESSION['CPREQUEST'] = $sCPRequest;

    $sEncData = "";
	$sRtnMsg = "";

    $sEncData	= `$sModulePath REQ $sSiteCode $sSitePw $sCPRequest $sReturnURL`;
    if($sEncData == -9) {
    	$sRtnMsg = "입력값 오류 : 암호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
    } else {
    	$sRtnMsg = "$sEncData 변수에 암호화 데이타가 확인되면 정상, 정상이 아닌 경우 리턴코드 확인 후 NICE평가정보 개발 담당자에게 문의해 주세요.";
    }
?>
<form name="form_ipin" method="post" action="https://cert.vno.co.kr/ipin.cb">
    <input type="hidden" name="m" value="pubmain">
    <input type="hidden" name="enc_data" value="<?=$sEncData?>">
    <input type="hidden" name="param_r1" value="">
    <input type="hidden" name="param_r2" value="">
    <input type="hidden" name="param_r3" value="">
</form>
<script>
    document.form_ipin.submit();
</script>