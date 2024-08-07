<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIpay mobile 승인결과 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$bank_array = array(
					"03" => "기업은행", "04" => "국민은행", "05" => "외환은행", "07" => "수협중앙회", "11" => "농협중앙회", "20" => "우리은행", "23" => "SC제일은행",
					"31" => "대구은행", "32" => "부산은행", "34" => "광주은행", "37" => "전북은행", "39" => "경남은행", "53" => "한국씨티은행", "71" => "우체국",
					"81" => "하나은행", "88" => "통합 신한은행 (신한,조흥은행)", "89" => "케이뱅크", "92" => "토스뱅크"
					);

	if(!count($_POST)){
		if($_GET['?P_STATUS']){
			$_GET['P_STATUS'] = $_GET['?P_STATUS'];
			unset($_GET['?P_STATUS']);
		}
		$_POST = $_GET;
	}

	define('__pg_card_pay.exe__', $_POST['P_OID']);
	makePGLog($_POST['P_OID'], 'INIPayMobileWeb Start');

	if($_POST['P_REQ_URL']) {
		$mid = ($cfg['card_test'] == "Y") ? 'INIpayTest' : $cfg['card_inicis_mobile_id'];
		$return = trim(comm($_POST['P_REQ_URL'], 'P_TID='.$_POST['P_TID'].'&P_MID='.$mid));
		$_POST['P_REQ_RET'] = $return;
		$return = explode('&', $return);
		foreach($return as $val) {
			list($var, $value) = explode('=', $val);
			$_POST[$var] = trim($value);
		}
	}

	makePGLog($_POST['P_NOTI'], 'INIpayMobileWeb rcv completed');

	// 카드 변수 처리
	$card_tbl	= $_POST['P_TYPE'] == 'VBANK' ? $tbl['vbank'] : $tbl['card'];
	$card_cd	= $_POST['P_FN_CD1'];
	$card_name	= iconv('euc-kr', _BASE_CHARSET_, $_POST['P_FN_NM']);
	$app_time	= $_POST['P_AUTH_DT'];
	$app_no		= $_POST['P_AUTH_NO'];
	$res_cd		= $_POST['P_STATUS'];
	$res_msg	= iconv('euc-kr', 'utf-8', addslashes($_POST['P_RMESG1']));
	$ono		= addslashes($_POST['P_OID']);
	$tno		= $_POST['P_TID'];
	$good_mny	= $_POST['P_AMT'];
	$goodname	= $_POST['P_RMESG3'];
	$pay_method = $_POST['P_TYPE'];
	$quota		= $_POST['P_RMESG2'];

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['date2'] > 0) return;

	if(!$ono) $ono = date('Ymd').'-temp'.rand(111,999);

	// 결제 결과 저장
	if($res_cd == '00') {
		if($_POST['P_TYPE'] == 'VBANK') {
			$bankname = $bank_array[$_POST['P_VACT_BANK_CODE']];
            if (!$bankname) {
                $bankname = $_POST['P_FN_NM'];
            }
			$account = $_POST['P_VACT_NUM'];
			if($bankname) {
				$add_sql .= ", bankname='$bankname' ,`account`='$account',  `depositor`='$depositor'";
			}
		} else {
			$add_sql .= ", card_cd='$card_cd', card_name='$card_name', app_time='$app_time', app_no='$app_no', quota='$quota'";
		}
		$pdo->query("
			update `$card_tbl` set
				stat=2,
				res_cd='$res_cd', res_msg='$res_msg', tno='$tno',
				ordr_idxx='$ono', good_mny='$good_mny', good_name='$goodname', buyr_name='$buyername',
				use_pay_method='$pay_method' $add_sql
			where wm_ono='$ono'
		");

		$_card = $pdo->assoc("select * from $card_tbl where wm_ono='$ono'");
		if($_card['guest_no']) $_SESSION['guest_no'] = $_card['guest_no'];
		else $_SESSION['guest_no'] = '';
		if($_card['member_no']) {
			$member = $pdo->assoc("select * from `$tbl[member]` where `no` = '$_card[member_no]'");
			$_SESSION['member_no'] = $_card['member_no'];
		}

		makePGLog($ono, 'INIpayMobileWeb success');

		$dacom_note_url = true;
		if($_POST['P_REQ_URL']) $dacom_note_url = false;
		include_once $engine_dir.'/_engine/order/order2.exe.php';

		exit('OK');
	} else {
		if($_POST['P_TYPE'] == 'VBANK' && $res_cd == '02') exit('OK');

		$pdo->query("update `$card_tbl` set `stat`='2', `res_cd`='$res_cd', `res_msg`='$res_msg', `ordr_idxx`='$ono', `tno`='$tno' where `wm_ono`='$ono'");

		// 카드결제 실패 처리
		$pdo->query("update `$tbl[order]` set `stat`=31 where `stat`=11 and `ono`='$ono'");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `stat`=11 and `ono`='$ono'");

		makePGLog($ono, 'INIpayMobileWeb failed');

		if($_POST['P_REQ_URL']) {
			alert('카드결제가 실패되었습니다.'.php2java($res_msg));

            $rurl = $root_url.'/shop/order.php';
            if ($_SESSION['cart_cache']) {
                $rurl .= '?cart_selected='.$_SESSION['cart_cache'];
            }
			msg('', $rurl);
			exit;
		}
		else exit("카드결제가 실패되었습니다.\\n".addslashes($res_msg));
	}

	exit($res_cd);

?>