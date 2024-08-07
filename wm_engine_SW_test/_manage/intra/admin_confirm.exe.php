<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
	$check_pno = $_POST['check_pno'];
	$name = addslashes(trim($_POST['name']));

	if($exec == 'del') {
		if($name == 'cfg_receive') {
			$res = $pdo->query("update `wm_mng` set `cfg_receive` = 'N' where `no` = '$no'");
			msg('삭제하였습니다.', 'reload', 'parent');
		}
		if($name == 'cfg_confirm') {
			$res = $pdo->query("update `wm_mng` set `cfg_confirm` = 'N' where `no` = '$no'");
			msg('삭제하였습니다.', 'reload', 'parent');
		}
	} else {
		$res = $pdo->query("truncate table `wm_cfg_confirm_list`");
		foreach($check_pno as $key => $val) {
			$name = addslashes($_POST['name'][$key]);
			$code = addslashes($_POST['code'][$key]);
			$res3 = $pdo->query("insert into `wm_cfg_confirm_list`(`use_yn`, `code`,`name`) value ('Y', '$code', '$name')");
		}
		msg('저장되었습니다', 'reload', 'parent');
	}
?>