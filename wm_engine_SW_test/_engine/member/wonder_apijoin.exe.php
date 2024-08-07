<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  원더 API 회원가입
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\HTTP\CurlConnection;

	include_once $engine_dir.'/_engine/include/common.lib.php';

	// Credentials 생성
	$wec_etc = new weagleEyeClient($GLOBALS['_we'], 'etc');
	$result = $wec_etc->call('getWonderKey');
	$wisa_wonder = json_decode($result);
	$credentials = base64_encode($wisa_wonder->wonder_wkey.':'.$wisa_wonder->wonder_wsecret);

	// 토큰 발급
	$curl = new CurlConnection(
		'https://login.wonders.app/wauth/token',
		'POST',
		'grant_type=client_credentials&scope=hosted_client_rw'
	);
	$curl->setHeader(array(
		'Authorization: Basic '.$credentials,
		'Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1',
	));
	$curl->exec();
	$result = $curl->getInfo();

	if($result['http_code'] != '200') {
		msg($result['http_code'].'위메프 로그인 인증 처리 중 오류가 발생하였습니다.', $root_url.'/_manage/?body=member@sns_login#tab4');
	}
	$token = json_decode($curl->getResult());

	// 클라이언트 발급
	$curl = new CurlConnection(
		'https://login-api.wonders.app/v2/alliance/clients',
		'POST',
		json_encode(array(
			'serviceName' => $cfg['company_mall_name'],
			'redirectUri' => $root_url.'/main/exec.php?exec_file=member/wonder_apijoin.exe.php',
			'contract' => array(
				'agreedTerms' => 'true',
				'requesterId' => $admin['admin_id'],
				'requesterName' => $admin['name'],
				'email' => ($admin['email']) ? $admin['email'] : $cfg['company_email'],
				'companyName' => $cfg['company_name'],
				'companyRegistrationNumber' => numberOnly($cfg['company_biz_num']),
			)
		))
	);
	$curl->setHeader(array(
		'Content-Type: application/json;charset=UTF-8',
		'Accept: application/json',
		'Authorization: Bearer '.$token->access_token
	));
	$curl->exec();
	$result = $curl->getInfo();
	if($result['http_code'] != '201') {
		msg('위메프 로그인 계정 생성 중 오류가 발생하였습니다.', $root_url.'/_manage/?body=member@sns_login#tab4');
	}
	$client = json_decode($curl->getResult(true));
	$clientId = $client->clientId;
	$clientSecret = $client->clientSecret;

	// 설정 저장
	$_POST['wonder_login_use'] = 'Y';
	$_POST['wonder_login_client_id'] = $clientId;
	$_POST['wonder_login_client_secret'] = $clientSecret;
	$no_reload_config = 'Y';
	include $engine_dir.'/_manage/config/config.exe.php';

	msg('위메프로그인 등록이 완료되었습니다.', '/_manage/?body=member@sns_login#tab4');

?>