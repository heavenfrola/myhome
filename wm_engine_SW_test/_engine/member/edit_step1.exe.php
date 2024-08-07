<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보 수정/비밀번호 검증
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	memberOnly();

	$sql_password = sql_password($_POST['pwd']);
	if(strlen($member['pwd']) == 64 && strlen($sql_password) == 16) {
		$sql_password = hash('sha256', $pwd);
	}

	if($sql_password!=$member[pwd]) msg(__lang_member_error_wrongPwd__);

	$_SESSION[pwd_check]=1;
	msg("",$root_url."/member/edit_step2.php","parent");

?>