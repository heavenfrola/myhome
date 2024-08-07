<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  리뷰 코멘트 작성
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	if($_REQUEST['exec'] == 'delete') {
		$no = numberOnly($_POST['no']);

		if(!$no) msg(__lang_common_error_required__, '/', 'parent');
		$data = $pdo->assoc("select * from `$tbl[review_comment]` where `no`='$no'");
		if(!$data[no]) msg(__lang_common_error_nodata__, '/', 'parent');
		$auth = getDataAuth2($data, 1);
		if($auth==3) {
			if(!$pwd) {
				msg(__lang_member_input_pwd__);
			}
			elseif(sql_password($pwd)!=stripslashes($data[pwd])) {
				msg(__lang_member_error_wrongPwd__);
			}
			$rURL = $_POST['listURL'];
		}
		else {
			$rURL = 'reload';
		}

		$pdo->query("delete from $tbl[review_comment] where no='$no'");
		$pdo->query("update $tbl[review] set total_comment=total_comment-1 where no='$data[ref]'");

		if($_POST['from_ajax'] == 'Y') exit;
		msg(__lang_common_error_deleted__, $rURL, 'parent');
	}
	else {
		checkBasic();

		if($cfg['product_review_comment'] != '2' && !$admin['no'] && $member['level'] > 1) {
			msg(__lang_board_info_auth3__);
		}

		$no = numberOnly($_POST['no']);
		$name = addslashes(del_html($_POST['name']));
		$pwd = $_POST['pwd'];
		$content = addslashes(del_html($_POST['content']));

		// 중복작성 방지
		$tmp = $pdo->assoc("select * from {$tbl['review_comment']} order by reg_date desc limit 1");

		if($tmp['ip'] == $_SERVER['REMOTE_ADDR'] && $tmp['content'] == stripslashes($content)) {
			msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
		}

		if(!$no) msg(__lang_common_error_required__);
		$data=$pdo->assoc("select * from `$tbl[review]` where `no`='$no'");
		if(!$data[no]) msg(__lang_common_error_nodata__);

		if ($cfg[boardFilter]) {
			if ($filterword = filterContent($cfg[boardFilter], $content)) msg(__lang_common_error_bannedWords__);
		}

	// 필수값
		if($member[level]==10) {
			checkBlank($name, __lang_member_input_name__);
			checkBlank($pwd, __lang_member_input_pwd__);
		}
		else {
			$name=$member[name];
		}

		checkBlank($content, __lang_common_input_content__);

		$sql="INSERT INTO `$tbl[review_comment]` ( `ref` , `name` , `member_id` , `member_no` , `pwd` , `content` , `ip` , `reg_date` ) ";
		$sql.="VALUES ( '$no' , '$name' , '$member[member_id]' , '$member[no]' , '$pwd' , '$content' , '{$_SERVER['REMOTE_ADDR']}' , '$now')";

		$pdo->query($sql);

		$sql="update `$tbl[review]` set `total_comment`=`total_comment`+1 where `no`='$no'";
		$pdo->query($sql);

		msg("","reload","parent");
	}

?>