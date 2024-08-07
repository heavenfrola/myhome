<?PHP

	$urlfix = 'Y';
	include $engine_dir."/_engine/include/common.lib.php";

	$this_root_url = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$this_root_url .= $_SERVER['HTTP_HOST'];

	/* +----------------------------------------------------------------------------------------------+
	' |  Connection
	' +----------------------------------------------------------------------------------------------+*/
	$sso = new weagleEyeClient($_we, 'account');
	$ssldata = $sso->call('getWisaSSL', array('session_id'=>addslashes($_GET['session_id']), 'root_url'=>$this_root_url, 'debug'=>$cfg['sso_debug'], 'charset'=>_BASE_CHARSET_));
	if(!$ssldata) msg("보안 전송이 실패되었습니다");


	/* +----------------------------------------------------------------------------------------------+
	' |  post 값 되돌리기
	' +----------------------------------------------------------------------------------------------+*/
	$extract		= explode("<sso_extract>", $ssldata);
	foreach ($extract as $pkey => $pval) {
		list($k, $v) = explode("<sso_split>", $pval);

		if(preg_match("/\]$/", $k)) {
			list($aname, $akey) = explode("[", $k);
			${$aname}[str_replace("]", "", $akey)] = $v;
			$_POST[$aname][str_replace("]", "", $akey)] =  $v;
		} else {
			${$k} = $_POST[$k] = $v;
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  스크립트 중계
	' +----------------------------------------------------------------------------------------------+*/
	$use_openssl	= true;

	if($exec == 'orderDetail') {
		$_SESSION['od_ono'] = $_POST['ono'];
		$_SESSION['od_phone'] = $_POST['phone'];

		msg('', $root_url.'/mypage/order_detail.php');
	}

	include_once $engine_dir."/_engine/".$exec_file;

?>