<?PHP

	if(!defined("_lib_inc")) exit();

	checkBasic();

	if($config[use_comment]=="N") msg(__lang_board_error_cmtNotUse__, "/", "parent");

	$no = numberOnly($_POST['no']);
	$content = addslashes(trim($_POST['content']));

	if(!$no) msg(__lang_common_error_required__);
	$data=$pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
	if(!$data[no]) msg(__lang_common_error_nodata__);

	checkWriteLimit("comment",1);

	if($cfg[boardFilter]) {
		if(filterContent($cfg['boardFilter'], $content)) msg(__lang_common_error_bannedWords__);
	}

	// 한글 필수 체크
	if ($cfg[board_chk_Korean] == "Y") if (!preg_match("/\p{Hangul}/u", $content)) msg(__lang_common_error_hangul__);

	// 차단 아이피 체크
	if ($cfg[boardDenyIP]) {
		$filters = explode(",", $cfg[boardDenyIP]);
		foreach ( $filters as $key => $val) {
			if ($_SERVER[REMOTE_ADDR] == trim($val)) msg(__lang_common_error_bannedIP__);
		}
	}

	$name = addslashes(trim($_POST['name']));
	$pwd = trim($_POST['pwd']);
	$content = addslashes(trim($_POST['content']));

	// 필수값
	if($member[level]==10) {
		checkBlank($name, __lang_member_input_name__);
		checkBlank($pwd, __lang_member_input_pwd__);
	}
	else {
		if($member[level]==1 && $_use[name_write]=='Y') {
			$name=$_POST[name];
		} else {
			$name=$member[name];
		}
	}

	$pwd=sql_password($pwd);

	$content = strip_script($content);
	if (!$content) msg(__lang_member_input_content__);

	if($_POST[cat] && strchr($root_url, "catboy")){ // 2007-01-30 : 캣보이 표정선택
		$add_q1=", `cat`";
		$add_q2=", '$cat'";
	}

	$tmp = $pdo->assoc("select * from {$mari_set['mari_comment']} order by `reg_date` desc limit 1");
	if ($tmp['ip'] == $_SERVER['REMOTE_ADDR'] && $tmp['name'] == $name && $tmp['content'] == stripslashes($content)) {
		msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
	}

	$sql="INSERT INTO `$mari_set[mari_comment]` ( `db` , `ref` , `name` , `member_id` , `member_no` , `pwd` , `content` , `ip` , `reg_date` $add_q1) ";
	$sql.="VALUES ('$db' , '$no' , '$name' , '$member[member_id]' , '$member[no]' , '$pwd' , '$content' , '{$_SERVER['REMOTE_ADDR']}' , '$now' $add_q2)";

	$pdo->query($sql);
	checkWriteInput("comment");
	putBBSPoint("comment");

	$sql="update `$mari_set[mari_board]` set `total_comment`=`total_comment`+1 where `no`='$no'";
	$pdo->query($sql);

	if(!$listURL) {
		$listURL="reload";
	}

	if($_POST['mari_blog'] == 'Y') javac("parent.getMariComment('$db', $no)");
	else msg("",$listURL,"parent");

?>