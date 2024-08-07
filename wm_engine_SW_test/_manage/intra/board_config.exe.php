<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷게시판권한 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	include $engine_dir."/board/include/lib.php";

	$exec = $_POST['exec'];
	$no = numberOnly($_POST['no']);
	$auth = $_POST['auth'];
	$delete_db = addslashes($_POST['delete_db']);
	$auth_list = $_POST['auth_list'];
	$auth_write = $_POST['auth_write'];
	$auth_view = $_POST['auth_view'];
	$auth_reply = $_POST['auth_reply'];
	$auth_comment = $_POST['auth_comment'];
	$auth_upload = $_POST['auth_upload'];
	$title = $_POST['title'];
	$upfile_ext = $_POST['upfile_ext'];
	$upfile_size = $_POST['upfile_size'];

	$_tbl=$tbl[intra_board_config];

	if($exec == "create"){
		$pdo->query("alter table $_tbl add unique index (db)");

		$db = time();
		$sql="insert into `$_tbl`(`db`, `title`) values('$db', '신규게시판')";
		$r = $pdo->query($sql);
		if($r) {
			msg("새로운 게시판이 생성되었습니다","reload","parent");
		}
	}elseif($exec == "delete"){

		$sql="select * from `$tbl[intra_board]` where `db`='$delete_db'";
		$res = $pdo->iterator($sql);
        foreach ($res as $data) {
			if($data[updir]) {
				deletePrdImage($data,1,2);
			}
		}

		$sql="delete from `$tbl[intra_board]` where `db`='$delete_db'";
		$pdo->query($sql);
		$sql="delete from `$tbl[intra_comment]` where `db`='$delete_db'";
		$pdo->query($sql);
		$sql="delete from `$_tbl` where `db`='$delete_db'";
		$pdo->query($sql);

		msg("삭제되었습니다","reload","parent");

	}


	for($ii=0; $ii<count($no); $ii++) {
		if($no[$ii]) {
			if($auth){
				$_sql="`auth_list`='$auth_list[$ii]', `auth_write`='$auth_write[$ii]', `auth_view`='$auth_view[$ii]', `auth_reply`='$auth_reply[$ii]', `auth_comment`='$auth_comment[$ii]', `auth_upload`='$auth_upload[$ii]'";
			}else{
				$upfile_size[$ii]=($upfile_size[$ii] > 5120) ? 5120 : $upfile_size[$ii];
				$_sql="`title`='$title[$ii]', `upfile_ext`='$upfile_ext[$ii]', `upfile_size`='$upfile_size[$ii]'";
			}

			$sql="update `$_tbl` set $_sql where `no`='$no[$ii]'";
			$pdo->query($sql);
		}
	}

	msg("적용되었습니다","reload","parent");

?>