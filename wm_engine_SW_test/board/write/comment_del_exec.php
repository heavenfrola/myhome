<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	checkBasic(2);

	$no = $_POST['no'];
	$pwd = $_POST['pwd'];
	if(!$no) $no = $_GET['no'];
	if(!$pwd) $pwd = $_GET['pwd'];

	$no = numberOnly($no);
	$pwd = trim($pwd);

	if(!$no) msg(__lang_common_error_required__, "/", "parent");
	$data=$pdo->assoc("select * from `$mari_set[mari_comment]` where `no`='$no' and `db`='$db'");
	if(!$data[no]) msg(__lang_common_error_nodata__, "/", "parent");
	$auth=getDataAuth($data,1);
	if($auth==3) {
		if(!$pwd) {
			msg(__lang_member_input_pwd__);
		}
		elseif(sql_password($pwd)!=stripslashes($data[pwd])) {
			msg(__lang_member_error_wrongPwd__);
		}
		$rURL = "?db=$data[db]&mari_mode=view@view&no=$data[ref]";
	}
	else {
		$rURL="reload";
	}

	$sql="delete from `$mari_set[mari_comment]` where `no`='$no'";
	$pdo->query($sql);

	$sql="update `$mari_set[mari_board]` set `total_comment`=`total_comment`-1 where `no`='$data[ref]'";
	$pdo->query($sql);

	msg(__lang_common_error_deleted__, $rURL, "parent");

?>