<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  보안서버 설정 가능 여부 체크
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['ssl_type'] == 'Y') {
		$url = 'https:'.preg_replace('/https?:/', '', $root_url).'/main/exec.php';

		$ret = comm($url.'?exec_file=common/sso.exe.php&ssl_test=Y&urlfix=Y');
		$ret = preg_replace('/^callback\((.*)\)$/', '$1', $ret);
		$ret = json_decode($ret);

		if($ret->https != 'on') {
			msg('보안인증서버 설치 여부가 확인되지 않습니다.\\n서버 관리자에게 문의해주세요.');
		}
	}

	$wec = new weagleEyeClient($_we, 'Etc');
	$wec->call('setExternalService', array(
		'service_name' => 'use_ssl',
		'use_yn' => $_POST['ssl_type'],
	));

	include 'config.exe.php';

	exit;

?>