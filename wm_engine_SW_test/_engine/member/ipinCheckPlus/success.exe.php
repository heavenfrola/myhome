<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $sitecode = $cfg['ipin_checkplus_id'];
    $sitepasswd = $cfg['ipin_checkplus_pw'];

    $cb_encode_path = $engine_dir.'/_engine/member/ipinCheckPlus/bin/CPClient';

    $enc_data = $_REQUEST["EncodeData"];
    $sReserved1 = $_REQUEST['param_r1'];
	$sReserved2 = $_REQUEST['param_r2'];
	$sReserved3 = $_REQUEST['param_r3'];

    if(preg_match('~[^0-9a-zA-Z+/=]~', $enc_data, $match)) exit('입력 값 확인이 필요합니다 : '.$match[0]);
    if(base64_encode(base64_decode($enc_data))!=$enc_data) exit('입력 값 확인이 필요합니다');
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved1, $match)) exit('문자열 점검 : '.$match[0]);
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved2, $match)) exit('문자열 점검 : '.$match[0]);
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReserved3, $match)) exit('문자열 점검 : '.$match[0]);

    if($enc_data != "") {
        $plaindata = `$cb_encode_path DEC $sitecode $sitepasswd $enc_data`;

		switch($plaindata) {
			case -1 :
	            $returnMsg = '암/복호화 시스템 오류';
			break;
			case -4 :
	            $returnMsg  = '복호화 처리 오류';
			break;
			case -5 :
				$returnMsg  = 'HASH값 불일치 - 복호화 데이터는 리턴됨';
			break;
			case -6 :
	            $returnMsg  = '복호화 데이터 오류';
			break;
			case -9 :
	            $returnMsg  = '입력값 오류';
			break;
			case -12 :
	            $returnMsg  = '사이트 비밀번호 오류';
			break;
		}
    

		if($returnMsg) exit($returnMsg);

		$ciphertime = `$cb_encode_path CTS $sitecode $sitepasswd $enc_data`;

		$parse_value = GetValue($plaindata);

		$requestnumber = $parse_value['REQ_SEQ'];
		$responsenumber = $parse_value['RES_SEQ'];
		$authtype = $parse_value['AUTH_TYPE'];
		$name = $parse_value['NAME'];
		$birthdate = $parse_value['BIRTHDATE'];
		$gender = $parse_value['GENDER'];
		$nationalinfo = $parse_value['NATIONALINFO'];
		$dupinfo = $parse_value['DI'];
		$conninfo = $parse_value['CI'];

		if(strcmp($_SESSION["REQ_SEQ"], $requestnumber) != 0) {
			exit('세션값이 다릅니다. 올바른 경로로 접근하시기 바랍니다');
		}
	}

    function GetValue($plaindata){

        $ret = array();

        $arr = explode(":", $plaindata);
        /*
        arr[0] 이후 부터 아래 형태이며 0은 arr[1]내 필드명의 길이
        홀수 : 필드명+다음값 길이
        짝수 : 값+다음값 길이
    	ex) arr[0]:7, arr[1]:REQ_SEQ5, arr[2]:abcde7, arr[3]:RES_SEQ3, arr[4]:zxc4, ,,,
        ret = REQ_SEQ : abcde, RES_SEQ : zxc, ,,,
        */
        $len = 0;
        foreach($arr as $k => $v){
            if($k==0){
                $len = $v;
            } elseif( ($k%2) != 0) {
                $str = substr($v, 0, $len);
                $ret[$str] = substr($arr[$k+1], 0, (int)str_replace($str, '', $v));
                $len = (int)str_replace($ret[$str], '', $arr[$k+1]);
            }
        }
        return $ret;
    }


	$_SESSION['ipin_res'] = array(
		'name' => iconv('euc-kr', _BASE_CHARSET_, $name),
		'birth' => $birthdate,
		'gender' => $gender
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