<?PHP

	// 구 우편번호 DB 접속
	$cfg['use_local_zipcode'] = 'Y';
	$cfg['zipcode_host'] = '';
	$cfg['zipcode_user'] = '';
	$cfg['zipcode_pwd'] = '';
	if(!$cfg['use_self_zipcode']) {
		$cfg['use_self_zipcode'] = 'N';
	}

	$cfg['zipcode_user'] = 'zipcode';
	$cfg['zipcode_pwd'] = 'wm2006zipcode';

	$cfg['juso_api_server'] = 1;
	if($cfg['juso_api_server'] == 2) { // 행자부 서버
		$cfg['juso_api_url'] = 'http://www.juso.go.kr/addrlink/addrLinkApi.do';
	} else { // 위사서버
		$cfg['juso_api_url'] = 'http://juso.wisa.ne.kr:8080/app/search/addrSearchApi.do';
	}

	// 비밀번호 암호화
	$cfg['pwd'] = 'Y';

	// ssl 정보
	$cfg['ssl_host'] = $root_url.'/main/exec.php';

	// 윙디스크 사용
	$cfg['disable_wingdisk'] = true;

	// convert
	$cfg['imagick_path'] = '/usr/local/bin';

	// 공지사항 URL
	define('_HOSTING_NOTICE_XML_', 'http://redirect.wisa.co.kr/center.xml.php');

	// mywisa.com
    if(defined('_WISA_CENTER_URL_') == false) {
    	define('_WISA_CENTER_URL_', 'https://wingstore.wisa.co.kr');
    }

?>