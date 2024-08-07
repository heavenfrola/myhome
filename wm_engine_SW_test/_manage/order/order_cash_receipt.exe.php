<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  현금영수증 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/shop.lib.php";

	$exec = $_POST['exec'];
	$ssmode	= numberOnly($_POST['ssmode']);
	if($_POST['check_pno']) $check_pno = $_POST['check_pno'];

	if(is_admin()) {
		if($exec != 'chgBnum' || ($exec == 'chgBnum' && $ssmode == '2')) {
			if(count($check_pno) < 1) msg('하나 이상의 현금영수증을 선택해주세요.');
			$ord_no = implode (",",$check_pno);
		}

		if(!$manage_url) {
			$tmp = parse_url(getURL());
			$manage_url = $tmp['scheme'].'://'.$tmp['host'];
		}
	}

	if($exec == "chgBnum") {
		$b_num = addslashes(trim($_POST['b_num']));

		if(empty($ssmode)) msg("잘못된 요청입니다.");
		if(empty($b_num)) msg("변경하실 사업자 번호를 입력하세요");

		switch($ssmode) {
			case '2':
				$where=" where `no` in ($ord_no)";
			break;
			case '3':
				$where="";
			break;
			case '4':
				$version="new";

				$_query_string = explode('&', trim(urldecode($_POST['query_string'])));
				foreach($_query_string as $key => $val) {
					$tmp = explode('=', $val);
					if($tmp[0] == 'body' || !$tmp[0] || !$tmp[1]) continue;
					${$tmp[0]} = $_GET[$tmp[0]] = $tmp[1];
				}

				include_once $engine_dir."/_manage/order/order_cash_receipt.php";

				if(empty($sql)) $where="";
				else {
					$res=$pdo->iterator($sql);
					$check_pno2 = array();
                    foreach ($res as $data) {
						$check_pno2[]=$data[no];
					}
					$total=count($check_pno2);
					$ord_no=implode(",", $check_pno2);
					if($ord_no) $where="where `no` in ($ord_no)";
					else msg("변경하실 현금영수증 건이 없습니다.");
				}
			break;
		}

		if($where) $where.=" and `stat`=1";
		else $where.=" where `stat`=1";

		if($total <= 0) $total=$pdo->row("select count(*) from `$tbl[cash_receipt]` $where");
		$pdo->query("update `$tbl[cash_receipt]` set `b_num`='$b_num' $where");
		msg($total."건의 현금영수증 건의 사업자 번호를 변경하였습니다.", "reload", "parent");
	}
	else{
		$ext = numberOnly($_POST['ext']);
		$ord = array();
		foreach($check_pno as $key=>$val) {
			$ord['cash_no'][] = $val;
		}

		$confirm_count = cashReceiptAuto($ord, 0, $ext);
		if($confirm_count>0) {
			if($ext == 1) {
				$ktype = "발급";
			}else {
				$ktype = "취소";
			}
			msg($confirm_count."개의 신청서가 ".$ktype."처리 되었습니다", "reload", "parent");
		}else {
			msg('처리된 내역이 없습니다.', "reload", "parent");
		}
	}

?>