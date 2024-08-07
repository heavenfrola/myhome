<?PHP

	include $engine_dir.'/_engine/include/member.lib.php';

	$exec = $_POST['exec'];
	$mno = numberOnly($_POST['mno']);
	$cnt = 0;
	$type = ($exec == 'remove') ? '삭제' : '휴면 해제';

	foreach($mno as $_no) {
		switch($exec) {
			case 'remove' :
				$pdo->query("insert into $tbl[delete_log] (type, deleted, title, admin, deldate) values ('M', '$_no', '$_no 회원 삭제', '$admin[admin_id]', '$now')");

				$pdo->query("delete from $tbl[member_deleted] where no='$_no'");
				if($pdo->lastRowCount() > 0) {
					$pdo->query("delete from {$tbl['member']} where no='$_no'");

                    // 예치금 및 적립금 내역 삭제
                    $pdd->query("delete from {$tbl['milage']} where member_no='$_no'");
                    $pdd->query("delete from {$tbl['emoney']} where member_no='$_no'");
            		$cnt++;
				}
			break;
			case 'restore' :
				$r = restoreDeleted($_no);
                if($r) $cnt++;
			break;
		}
	}
	msg($cnt.' 건의 회원이 '.$type.' 되었습니다.', 'reload', 'parent');

?>