<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $sitecode = $cfg['ipin_checkplus_id'];
    $sitepasswd = $cfg['ipin_checkplus_pw'];

    $cb_encode_path = $engine_dir.'/_engine/member/ipinCheckPlus/bin/CPClient';

    $enc_data = $_REQUEST["EncodeData"];		// 암호화된 결과 데이타
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
	}
	exit($returnMsg);
