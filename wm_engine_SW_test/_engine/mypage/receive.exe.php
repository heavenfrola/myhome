<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  수취완료 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	if($_POST['allat_result_cd']) {
		foreach($_GET as $key => $val) {
			if(preg_match('/ono$/', $key)) {
				$ono = $val;
				break;
			}
		}
		$allat_result_cd = $_POST['allat_result_cd'];
		if($allat_result_cd != '0000') {
			$allat_result_msg = mb_convert_encoding($_POST['allat_result_msg'], _BASE_CHARSET_, 'euckr');
			alert(php2java($allat_result_msg));
			exit;
		}
	}

	$ono = addslashes(trim($_GET['ono']));
	if(!$ono) msg(__lang_mypage_input_ono__);
	$data = get_info($tbl['order'], 'ono', $ono);
	if(!$data[no]) msg(__lang_mypage_error_onoNotExist__);

	if($data['member_no']) {
		if($data['member_no'] != $member['no']) msg(__lang_mypage_error_notOwnOrd__);
		if(!$rURL) $rURL = $root_url."/mypage/order_list.php";
	} else {
		if($_SESSION['od_ono'] != $data['ono']) msg(__lang_mypage_error_notOwnOrd__);
	}

	if($confirm && $confirm == 'confirm_dny') {
		$msg = __lang_mypage_error_escrowDeny__;
	} else {
		if($cfg['card_pg'] == 'inicis' && $cfg['pg_version'] == 'INILite') $pdo->query("update `$tbl[order]` set `confirm_yn`='Y' where `ono`='$ono'");

		$ext=5;
		$from_receive="Y";
		include_once $engine_dir."/_manage/order/order_stat.php";

		$msg = __lang_mypage_error_escrowAccept__;
	}

	if($data['pay_type'] == 17) { // 페이코 수취확인
		escDlvRegist($data);
	}

	msg($msg, 'reload', 'parent');

?>