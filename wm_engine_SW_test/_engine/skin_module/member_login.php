<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  로그인
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['login_form_start'] = "<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkLoginFrm(this)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/login.exe.php\">
<input type=\"hidden\" name=\"rURL\" value=\"".$rURL."\">
<input type=\"hidden\" name=\"urlfix\" value=\"Y\">
";
	$_replace_code[$_file_name]['login_form_end']="</form>";
	$_replace_code[$_file_name]['ord_form_start']="<form method=\"post\" action=\"".$root_url."/mypage/order_detail.php\" onSubmit=\"return checkGuestOrderFrm(this)\" style=\"margin:0px\">
<input type='hidden' name='exec' value='orderDetail' />
";
	$_replace_code[$_file_name]['ord_form_end']="</form>";

	if($err){
		$_replace_code[$_file_name]['login_fail_msg']=getModuleContent("login_fail_msg");;
	}

	if($cfg['order_auth'] == 10 && $cfg['order_style'] == 'login' && $_GET['guest'] == 'true' && $member['no'] < 1) {
		$_line = getModuleContent("guest_order");
		$_tmp  = $_GET['rURL'] ? $_GET['rURL'] : $root_url.'/shop/order.php';
		$_tmp .= (strpos($_tmp, '?') == false) ? '?' : '&';
		$_tmp .= 'guest_ord='.$_SESSION['guest_no'];
		$_gc['guset_order_btn'] = $_tmp;
		$_replace_code[$_file_name]['guest_order'] = lineValues('guest_order', $_line, $_gc);
	}

?>