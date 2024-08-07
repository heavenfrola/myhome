<?PHP

	$mywisa_url = _WISA_CENTER_URL_;
	$obody = urlencode($_GET['obody']);
	if(strstr($obody, 'wftp')) {
		$mywisa_url = 'http://mywing2.wisa.co.kr';
	}

	$_obody = explode('@', urldecode($obody));
	if(count($_obody) == 3) {
		$obody = 'body='.$_obody[1].'@'.$_obody[2];
	}

	$key_code = $_we['wm_key_code'];
	$partner_code = 'wisa';

	if(!trim($key_code)) msg('사이트의 인증이 확인되지 않았습니다', 'back');

	$auth = $pdo->assoc("select * from $tbl[mng_auth] where admin_no='$admin[no]'");

	$manage_url = (($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

	$_send_site_info = $_SERVER['HTTP_HOST']."^";
	$_send_site_info .= $admin['admin_id']."^";
	$_send_site_info .= mb_convert_encoding($admin['name'], 'euc-kr', _BASE_CHARSET_)."^";
	$_send_site_info .= $admin['no']."^";
	$_send_site_info .= $admin['level']."^";
	$_send_site_info .= $cfg['ver']."^";
	$_send_site_info .= $cfg['pver']."^";
	$_send_site_info .= $admin['auth']."^";
	$_send_site_info .= $manage_url."^";
	$_send_site_info .= $partner_code.'^';
	$_send_site_info .= $auth['customer'].'^';
	$_send_site_info .= $auth['wing'].'^';
	$_send_site_info .= $auth['order'].'^';
	$_send_site_info .= numberOnly($admin['phone']).'^';
	$_send_site_info .= numberOnly($admin['cell']);

	$encode = '';
	$length = strlen($_send_site_info);
	for ($i = 0; $i < $length; $i++) {
		$encode .= dechex(ord($_send_site_info[$i]));
	}
	$send_site_info = $encode;

	$pdo->query("insert into `$tbl[mng_cs_log]` (`admin_no`, `admin_id`, `admin_level`, `send_site_info`, `req_now`, `reg_date`) values('$admin[no]', '$admin[admin_id]', '$admin[level]', '$send_site_info', '$now', '".time()."')");

?>
<form name="clientfrm" method="post" action="<?=$mywisa_url?>/index.php" style="display:none">
	<input type="hidden" name="body" value="main@login.exe">
	<input type="hidden" name="exec" value="login_request">
	<input type="hidden" name="wdomain" value="<?=$wdomain?>">
	<input type="hidden" name="key_code" value="<?=$key_code?>">
	<input type="hidden" name="partner_code" value="<?=$partner_code?>">
	<input type="hidden" name="send_site_info" value="<?=$send_site_info?>">
	<input type="hidden" name="req_now" value="<?=$now?>">
	<input type="hidden" name="site_root_url" value="http://<?=$_SERVER['HTTP_HOST']?>">
	<input type="hidden" name="obody" value="<?=$obody?>">
	<input type="hidden" name="site_br_title" value="<?=$cfg[br_title]?>">
</form>

<script language="JavaScript">
	f=document.clientfrm;
	f.submit();
</script>