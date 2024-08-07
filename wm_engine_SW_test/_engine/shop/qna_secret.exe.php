<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 비밀글 패스워드 확인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$pwd = $_POST['pwd'];
	$no = numberOnly($_POST['no']);

	checkBasic();
	checkBlank($no, __lang_common_error_required__);
	checkBlank($pwd, __lang_member_input_pwd__);

	$data = $pdo->assoc("select no, member_no, pwd from $tbl[qna] where no='$no'");
	if(!$data['no']) msg(__lang_common_error_nodata__);
	if($data['member_no'] && $data['member_no'] != $member['no']) {
		msg(__lang_common_error_secretArticleOnly__);
	}
	if($data['pwd'] != sql_password($pwd) && $data['pwd'] != substr(sql_password($pwd),0,20)) {
		msg(__lang_member_error_wrongPwd__);
	}


	$_SESSION['view_qna_secret'] = $data['no'];
	msg('', 'reload', 'parent');

?>