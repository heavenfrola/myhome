<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$block_ip=numberOnly($_POST['block_ip_0']).'.'.numberOnly($_POST['block_ip_1']).'.'.numberOnly($_POST['block_ip_2']).'.'.numberOnly($_POST['block_ip_3']);
	$ii=0;
	$no=numberOnly($_POST['no']);
	$exec=$_POST['exec'];
	$check_pno=$_POST['check_pno'];
	$title=addslashes(trim($_POST['title']));
	$msg=strip_tags($_POST['msg']);

	if($exec=="msg") {
		$fp=fopen($root_dir.'/_data/ip_msg.txt', 'w');
		fwrite($fp, $msg);
		fclose($fp);
		msg('등록되었습니다', './?body=config@ip_block', 'parent');
	}

	if($exec=="delete") {
		foreach($check_pno as $val) {
			$val=numberOnly($val);
			$ip_replace=$pdo->row("select `ip` from `".$tbl['deny_ip']."` where `no`='$val'");
			$res=$pdo->query("delete from `".$tbl['deny_ip']."` where `no`='$val'");
			if($res) $ii++;
		}

		ipwrite();
		msg("$ii 개의 아이피를 삭제하였습니다.", 'reload', 'parent');
	}

	if(!$no) {
		$sql="INSERT INTO `".$tbl['deny_ip']."` (`no`, `ip`, `title`, `admin_no`, `admin_id`, `reg_date`) ";
		$sql.="VALUES ('$no', '$block_ip' , '$title', '$admin[no]', '$admin[admin_id]', '$now')";
		$pdo->query($sql);

		ipwrite();
		msg('등록되었습니다', './?body=config@ip_block', 'parent');

	} else {
		$data=$pdo->assoc("select * from `".$tbl['deny_ip']."` where `no`='$no'");
		$ip_replace=$data['ip'];
		if(!$data['no']) msg('존재하지 않는 아이피입니다.', 'back', '');

		$sql="update `".$tbl['deny_ip']."` set `ip`='$block_ip', `title`='$title', `admin_no`='$admin[no]', `admin_id`='$admin[admin_id]', `reg_date`='$now' where `no`='$no'";
		$pdo->query($sql);

		ipwrite();
		msg('등록되었습니다', './?body=config@ip_block', 'parent');
	}

	function ipwrite() {
		global $root_dir, $tbl, $pdo;
		$sql="select `ip` from `".$tbl['deny_ip']."` as a";
		$res = $pdo->iterator($sql);
		unlink($root_dir.'/_data/ip_block.txt');
        foreach ($res as $data) {
			$fp=fopen($root_dir.'/_data/ip_block.txt', 'a');
			fwrite($fp, $data['ip']."\n");
			fclose($fp);
		}
	}

?>