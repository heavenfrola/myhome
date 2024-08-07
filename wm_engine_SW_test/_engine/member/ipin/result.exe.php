<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $sSiteCode = $cfg['ipin_id'];
    $sSitePw = $cfg['ipin_pw'];

	$sEncData = "";
	$sDecData = "";

	$sRtnMsg = "";
	$sModulePath = $engine_dir.'/_engine/member/ipin/bin/IPINClient';;

	$sEncData = $_POST['enc_data'];

	if(preg_match('~[^0-9a-zA-Z+/=]~', $sEncData, $match)) exit('입력 값 확인이 필요합니다.');
    if(base64_encode(base64_decode($sEncData))!=$sEncData) exit('입력 값 확인이 필요합니다.');

	$sCPRequest = $_SESSION['CPREQUEST'];

    if($sEncData != "") {
       	$sDecData = `$sModulePath RES $sSiteCode $sSitePw $sEncData`;

    	if($sDecData == -9) {
    		$sRtnMsg = "입력값 오류 : 복호화 처리시, 필요한 파라미터값의 정보를 정확하게 입력해 주시기 바랍니다.";
    	} else if($sDecData == -12) {
    		$sRtnMsg = "NICE평가정보에서 발급한 개발정보가 정확한지 확인해 보세요.";
    	} else {
    	    		$arrData = split("\^", $sDecData);
    		$iCount = count($arrData);

    		if($iCount >= 5) {
				$strResultCode	= $arrData[0];			// 결과코드
				if($strResultCode == 1) {
					$strCPRequest	= $arrData[8];			// CP 요청번호

					if($sCPRequest == $strCPRequest) {
						$sRtnMsg = "사용자 인증 성공";

						$strVno      		= $arrData[1];	// 가상주민번호 (13자리이며, 숫자 또는 문자 포함)
						$strUserName		= $arrData[2];	// 이름
						$strDupInfo			= $arrData[3];	// 중복가입 확인값 (64Byte 고유값)
						$strAgeInfo			= $arrData[4];	// 연령대 코드 (개발 가이드 참조)
					    $strGender			= $arrData[5];	// 성별 코드 (개발 가이드 참조)
					    $strBirthDate		= $arrData[6];	// 생년월일 (YYYYMMDD)
					    $strNationalInfo	= $arrData[7];	// 내/외국인 정보 (개발 가이드 참조)
					} else {
						$sRtnMsg = "CP 요청번호 불일치 : 세션에 넣은 $sCPRequest 데이타를 확인해 주시기 바랍니다.";
					}
				} else {
					$sRtnMsg = "리턴값 확인 후, NICE평가정보 개발 담당자에게 문의해 주세요. [$strResultCode]";
				}
    		} else {
    			$sRtnMsg = "리턴값 확인 후, NICE평가정보 개발 담당자에게 문의해 주세요.";
    		}
    	}
    } else {
    	$sRtnMsg = "처리할 암호화 데이타가 없습니다.";
    }

	$_SESSION['ipin_res'] = array(
		'name' => iconv('euc-kr', _BASE_CHARSET_, $strUserName),
		'birth' => $strBirthDate,
		'gender' => $strGender
	);
$reUrl = 'join_step2.php';
?>
<form id='joinAgreeFrm' name="joinAgreeFrm" method="post" action="../member/<?=$reUrl?>" style="margin:0px" >
    <input type="hidden" name="agree" value="Y">
    <input type="hidden" name="privacy" value="Y">
    <input type="hidden" name="member_type" value="Y">
</form>
<script type="text/javascript">
    var browser_type = "<?=$_SESSION['browser_type']?>";
    var f = document.getElementById('joinAgreeFrm');
    if (browser_type=='pc') {
        f.target = 'Parent_window';
        window.close('_self');
        f.submit();
    } else {
        //페이지 이동
        f.target = '_self';
        f.submit();
    }
</script>