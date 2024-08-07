<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 예치금/적립금 삭제
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/milage.lib.php";

	checkBasic();

	$_now = date('Y/m/d',$now);

	$no = numberOnly($_POST['no']);
	if($no) $mno = array($no);
	$tbn = addslashes($_POST['tbn']);

	if($_POST['exec'] == "cancel"){
		foreach($mno as $_no) {
			$amount = $pdo->row("select `amount` from `$tbl[$tbn]` where `no` = '$_no'");

			if($tbn == 'emoney') ctrlEmoney("-",3,$amount,$member, "출석체크이벤트 -  $_now 취소", "", $admin[admin_id]);
			else ctrlMilage("-",3,$amount,$member, "출석체크이벤트 -  $_now 취소", "", $admin[admin_id]);

			$pdo->query("update `$tbl[milage]` set title=concat(title, '(취소)') where `no`='$_no'");
		}
	}

	if($_POST['exec'] == 'delete') {
		foreach($mno as $_no) {
			$pdo->query("delete from `$tbl[$tbn]` where `no`='$_no'");
		}
	}

	msg('', 'reload', 'parent');

?>