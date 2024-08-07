<?PHP

	include_once $engine_dir.'/_engine/include/member.lib.php';

	$exec = $_POST['exec'];

	switch($exec) {
		case 'restore' :
			foreach($mno as $member_no) {
				if(restoreDeleted($member_no)) {
					$cnt++;
				}
			}

			msg("[$cnt] 명의 회원이 휴면 해제 처리 되었습니다.", 'reload', 'parent');
		break;
		case 'remove' :
			foreach($mno as $member_no) {
				$pdo->query("delete from $tbl[member] where no='$member_no'");
				$pdo->query("delete from $tbl[member_deleted] where no='$member_no'");
			}

			msg("[$cnt] 명의 회원이 삭제처리 되었습니다.", 'reload', 'parent');
		break;
	}

?>