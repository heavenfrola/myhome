<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  비밀번호 찾기 / 변경처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	checkBasic(2);

	$key = trim(addslashes($_POST['key']));
	$pwd = $_POST['pwd'];
	$ori_pwd = hash('sha256', $_POST['ori_pwd']);
	$next_change = $_GET['next_change'];
    $key_enc = aes128_encode($key, 'pwd_log');
	if(!$member['no']) $data=$pdo->assoc("select * from `$tbl[pwd_log]` where `key`='$key_enc' limit 1");
	if($member['no']) {
		$data['member_no'] = $member['no'];
		$data['member_id'] = $member['member_id'];
	}

	$data2=$pdo->row("select `pwd` from `$tbl[member]` where `no`='$member[no]'");
	if($next_change == 'Y') {
		$sql="update `$tbl[member]` set `change_pwd_date` = '$now', `change_pwd_next` ='Y' where `no`='$member[no]' and `member_id`='$member[member_id]' limit 1";
		$r=$pdo->query($sql);
		msg('', $root_url."/member/login.php?rURL=".urlencode($root_url), "parent");
	}

	list($pwd, $addq)=checkPwd($pwd); // 비번 체크 (비교,암호화)
	if($member['no']) {
		$htmlcontent = file_get_contents($root_dir.'/_skin/'.$design['skin'].'/CORE/member_modify_pwd.wsr');
		if($pwd == $ori_pwd) msg(__lang_member_info_oritPwd__);
		if(strpos($htmlcontent, 'ori_pwd')) {
			if($data2!= $ori_pwd && $cfg['use_pwd_change'] == 'Y') msg(__lang_member_error_oripwd__);
		}
	}
	if((!$data['no'] || $data['stat'] != '1') && !$member['no']) msg(__lang_member_error_changePwdExp__, $root_url, "parent");

	if(!$member['no'])$pdo->query("update `$tbl[pwd_log]` set `stat`='2' where `key`='$key_enc' limit 1");

	if($cfg['use_pwd_change'] == 'Y') {
		$member_update = ", `change_pwd_date` = '$now', `change_pwd_next` ='N'";
	}

	$sql="update `$tbl[member]` set `pwd`='$pwd' ".$member_update." where `no`='$data[member_no]' and `member_id`='$data[member_id]' limit 1";
	$r=$pdo->query($sql);

	if(!$r) msg(__lang_member_error_changePwd__);

	msg(__lang_member_info_changePwdComp__, $root_url."/member/login.php?rURL=".urlencode($root_url), "parent");

?>