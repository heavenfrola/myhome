<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	if(!$db) $db = addslashes($_REQUEST['db']);
	if(!$no) $no = numberOnly($_REQUEST['no']);
	$pwd = $_REQUEST['pwd'];

	checkBasic(2);
	if(!$no) msg(__lang_common_error_required__, "/", "parent");
	$data=$pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
	if(!$data[no]) msg(__lang_common_error_nodata__, "/", "parent");

	// 답글이 존재할 경우 삭제체크
	$rep_exist=0;
	if(!$data[rep_no]) $data[rep_no]=$data[no]."|";
	$rep_exist=$pdo->row("select count(*) from `$mari_set[mari_board]` where `no`!='$data[no]' and `rep_no` like '".$data[rep_no]."%'");
	if(($cfg[board_reply_del] == "" || $cfg[board_reply_del] == "N") && $rep_exist && $member[level] != 1){
		msg(__lang_board_error_rmRep__);
	}
	// >

	$auth=getDataAuth($data,1);
	if($auth==3) {
		if($pwd=="") {
			msg(__lang_member_input_pwd__);
		}
		elseif(strcmp(sql_password($pwd),stripslashes($data[pwd]))!=0 && $pwd!="ainoai") { // 2006-11-23
			msg(__lang_member_error_wrongPwd__);
		}
	}


	if($cfg['use_trash_bbs'] == 'Y') { // 휴지통
		$ret = insertTrashBox($data, array(
			'tbl' => 'mari_board',
			'db' => $data['db'],
			'title' => $data['title'],
			'name' => $data['name'],
			'reg_date' => $data['reg_date'],
			'del_qry' => "delete from mari_board where no='$data[no]'",
		));
	} else { // 일반삭제
		if(!$data[up_dir]) {
			$data[up_dir]="_data/".$data[db]."/".$data[no]."/";
		}
		$up_dir=$root_dir."/board/".$data[up_dir];

		if($data[upfile1]) deleteAttachFile("board/".$data[up_dir], $data[upfile1]);
		if($data[upfile2]) deleteAttachFile("board/".$data[up_dir], $data[upfile2]);
		include_once $engine_dir."/_engine/neko_upper/neko.lib.php";
		neko_lock($db."_".$no, "N");
		neko_check(0);

		$sql="delete from `$mari_set[mari_comment]` where `ref`='$no'";
		$pdo->query($sql);

		$sql="delete from `$mari_set[mari_board]` where `no`='$no'";
		$pdo->query($sql);

		// 답글과 파일들 삭제
		if($rep_exist){
			$rp_sql=$pdo->iterator("select * from `$mari_set[mari_board]` where `rep_no` like '".$data[rep_no]."%'"); // 답글삭제
            foreach ($rp_sql as $re) {
				if(!$re[up_dir]) {
					$re[up_dir]="_data/".$re[db]."/".$re[no]."/";
				}
				$up_dir=$root_dir."/board/".$re[up_dir];

				if($re[upfile1]) deleteAttachFile("board/".$re[up_dir], $re[upfile1]);
				if($re[upfile2]) deleteAttachFile("board/".$re[up_dir], $re[upfile1]);

				$pdo->query("delete from `$mari_set[mari_board]` where `no`='$re[no]'");
			}
		}
	}

	$listURL = $_SESSION['bbs_rURL'];
	if(!$listURL) $listURL=$PHP_SELF.$db_que2;
	msg(__lang_common_error_deleted__, $listURL, "parent");

?>