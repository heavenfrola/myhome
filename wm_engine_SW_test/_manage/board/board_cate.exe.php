<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 분류 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$db = addslashes(trim($_POST['db']));
	$exec = addslashes(trim($_POST['exec']));

	if(!$db) {
		msg('필수값(db)이 없습니다.', 'back');
	}

	if($exec == 'new') {
		$name=addslashes(trim($_POST['name']));
		if(!$name) {
			msg("필수값(name)이 없습니다","back");
		}

		$max=$pdo->row("select max(`sort`) from `mari_cate` where `db`='$db'");
		$max++;

		$sql="INSERT INTO `mari_cate` ( `db` , `name` , `sort`) VALUES ( '$db', '$name' , '$max')";
		$pdo->query($sql);

		msg("추가 되었습니다","reload","parent");
	}
	elseif($exec=="delete") {
		$delete_cate = numberOnly($_POST['delete_cate']);
		foreach($delete_cate as $key=>$val) {
			if($val) {
				$sql="delete from `mari_cate` where `no`='$val'";
				$pdo->query($sql);

				$sql="delete from `mari_board` where `cate`='$val'";
				$pdo->query($sql);
			}
		}

		msg("선택한 카테고리가 모두 삭제되었습니다","reload","parent");
	}
	else {
		$no = numberOnly($_POST['no']);
		$name = $_POST['name'];
		$sort = numberOnly($_POST['sort']);
		foreach($no as $key=>$val) {
			$name[$key]=addslashes($name[$key]);
			if($name[$key]) {
				$sql="update `mari_cate` set `name`='$name[$key]', `sort`='$sort[$key]' where `no`='$val'";
				$pdo->query($sql);
			}
		}
		msg("적용되었습니다","reload","parent");
	}

?>