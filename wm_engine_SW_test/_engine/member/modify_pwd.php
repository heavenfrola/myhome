<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  비밀번호 찾기 프론트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	common_header();

	$key = addslashes(trim($_GET['key']));
	if(!$member['no']) {
        $limittime = strtotime('-10 minutes');
        $key_enc = aes128_encode($key, 'pwd_log');
		$data = $pdo->assoc("select * from `$tbl[pwd_log]` where `key`='$key_enc' and reg_date > '$limittime' order by no desc limit 1");
		if(!$data['no'] || $data['stat'] != '1') msg(__lang_member_error_changePwdExp__, $root_url);
	} else {
        $data = array(
            'member_id' => $member['member_id'],
            'member_name' => $member['name'],
        );
    }

?>
<script type='text/javascript'>
function chkPwdFrm(f){
	if (f.pwd[0].value.length<4){
		alert(_lang_pack.member_error_pwdminlen);
		f.pwd[1].value='';
		f.pwd[0].focus();
		return false;
	}
	if (!CheckType(f.pwd[0].value,PASSWORD))	{
		alert(_lang_pack.member_info_pwd2);
		f.pwd[1].value='';
		f.pwd[0].focus();
		return false;
	}
	if(!checkBlank(f.pwd[1], _lang_pack.member_input_cpwd)) return false;
	if (f.pwd[0].value!=f.pwd[1].value)
	{
		alert(_lang_pack.member_error_cpwd);
		f.pwd[0].value=f.pwd[1].value='';
		f.pwd[0].focus();
		return false;
	}
}
</script>
<?php ob_start(); ?>
<table border="0" align="center" cellpadding="0" cellspacing="0">
<form name="pwdFrm" method="post" action="<?=$root_url?>/main/exec.php" target="hidden<?=$now?>" onSubmit="return chkPwdFrm(this);">
<input type="hidden" name="exec_file" value="member/modify_pwd.exe.php">
<input type="hidden" name="key" value="<?=$key?>">
	<tr>
		<td width="150" height="30">아이디</td>
		<td><?=$data['member_id']?></td>
	</tr>
	<tr>
		<td height="30">성명</td>
		<td><?=$data['member_name']?></td>
	</tr>
	<tr>
		<td height="30">변경할 비밀번호</td>
		<td><input type="password" name="pwd[]" id="pwd" maxlength="10" class="input"></td>
	</tr>
	<tr>
		<td height="30">변경할 비밀번호 확인</td>
		<td><input type="password" name="pwd[]" id="pwd" maxlength="10" class="input"></td>
	</tr>
	<tr align="center" height="40">
		<td colspan="2"><input type="submit" value="변경하기"></td>
	</tr>
</form>
</table>
<?php

	$_tmp_content=ob_get_contents();
	ob_end_clean();

	$_tmp_file_name = 'member_modify_pwd.php';

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

	if(@file_exists($root_dir."/_include/header.php")) include $root_dir."/_include/header.php";

	if(@file_exists($root_dir."/_template/member/modify_pwd.php")){
		include $root_dir."/_template/member/modify_pwd.php";
	}else{
		echo $_tmp_content;
	}
	if(@file_exists($root_dir."/_include/footer.php")) include $root_dir."/_include/footer.php";

?>
