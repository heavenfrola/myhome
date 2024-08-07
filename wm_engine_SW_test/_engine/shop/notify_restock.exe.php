<?php

/* +----------------------------------------------------------------------------------------------+
' | 재입고요청 처리
' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir.'/_engine/include/wingPos.lib.php';

$exec = $_POST['exec'];

// POST값 처리
$param = new stdClass;
foreach($_POST as $key => $value) {
	$param->$key = addslashes(trim($value));
}

if($exec == 'getDetailPrice') {
	$pno = addslashes($_POST['pno']);
	$prd = checkPrd($pno, false);
	$prd['pno'] = $prd['no'];
	$prd['cno'] = 1;
	$prd['buy_ea'] = 1;

	for($i = 1; $i <= $_POST['notify_restock_opt_count']; $i++) {
		$item_no = $_POST['notify_restock_option'.$i];
		$add_price = $pdo->row("select add_price from $tbl[product_option_item] where no='$item_no'");
		$prd['sell_prc'] += $add_price;
	}

	include_once $engine_dir.'/_engine/include/cart.class.php';
	$prdCart = new OrderCart();
	$prdCart->skip_dlv = 'Y';
	$prdCart->addCart($prd);
	$prdCart->complete();

	header('Content-type:application/json; charset='._BASE_CHARSET_);
	exit(json_encode(array(
		'pay_prc' => $prdCart->getData('pay_prc'),
		'pay_prc_c' => parsePrice($prdCart->getData('pay_prc'), true),
		'pay_prc_one' => ($prdCart->getData('pay_prc')/$prd['buy_ea']),
		'prd_prc' => $prdCart->getData('sum_prd_prc'),
		'prdcpn_no' => str_replace(',', '@', $prdCart->getData('set_prdcpn_no')),
		'cpn_pay_type' => (int)$cpn_pay_type,
	)));
}

if(strpos($exec, "ajax_") === False) {
	checkBlank($param->buyer_cell, __lang_notify_restock_input_cell__);
	checkBlank($param->buyer_cell_agree, __lang_notify_restock_confirm_agree__);
}

for($i=1; $i<=$param->notify_restock_opt_count; $i++) {
	checkBlank($param->{"notify_restock_option_no".$i}, __lang_notify_restock_select_option__);
	$copt[] = $param->{"notify_restock_option_no".$i};
}

$param->buyer_cell = numberOnly($param->buyer_cell);


switch($exec) {
	case "insert":
		$prd = checkPrd($param->pno, false);
		$opts = makeComplexKey(implode('_', $copt));
		$complex_no = $pdo->row("select complex_no from erp_complex_option where pno='$prd[parent]' and opts='$opts' and del_yn='N'");
		$nowtime = time();
		$expiretime = ($cfg['notify_restock_expire']) ? strtotime($cfg['notify_restock_expire']) : "";
		if($expiretime) $expire_sql = " AND reg_date >= '$expiretime' ";

		// 옵션정보
		$_split_big = $_split_small = "";
		for($i=0; $i<count($copt); $i++) {
			$opt_sql = "SELECT
                          poi.`iname` as name, pos.`name` as opt_name
                          , poi.`no`, poi.`opno`
                        FROM
                          `wm_product_option_item` poi LEFT JOIN `wm_product_option_set` pos ON poi.`opno` = pos.`no`
                        WHERE
                          poi.`no` = '$copt[$i]' ";
			$opt_result = $pdo->assoc($opt_sql);

			// $option 용 데이터 처리
			$_split_small = $opt_result['opt_name'] . " : " . $opt_result['name'];
			if($_split_big != "") $_split_big .= ", ";
			$_split_big .= $_split_small;
		}
		$option = $_split_big;
		unset($_split_big, $_split_small);

		// 중복신청 체크 (만료기준일내)
		$count_sql = "SELECT count(no) cnt FROM $tbl[notify_restock] WHERE del_stat='N' AND stat IN (1) $expire_sql AND pno='$prd[parent]' AND member_no='$member[no]' AND buyer_cell='$param->buyer_cell' AND complex_no='$complex_no' ";
		$count_result = $pdo->assoc($count_sql);
		if($count_result['cnt'] > 0) {
			msg(__lang_notify_restock_error_duplication__);
		} else {
			$sql = "INSERT INTO $tbl[notify_restock]
                      (`pno`, `member_no`, `complex_no`, `buyer_cell`, `option`, `reg_date`)
                    VALUES
                      ('$prd[parent]', '$member[no]', '$complex_no', '$param->buyer_cell', '$option', '$nowtime')
                    ";
			$result = $pdo->query($sql);
			if($result) {
				// 재입고 알림 신청 문자 발송 Start ===============================
				// 설정값에따라 대상에 문자발송여부 설정
				$_send_sms = false;
				switch($cfg['notify_restock_target']) {
					// 전체발송
					case "1":
						$_send_sms = true;
						break;
					// 회원만
					case "2":
						if($member['no']) $_send_sms = true;
						break;
					// 비회원만
					case "3":
						if(!$member['no']) $_send_sms = true;
						break;
				}
				if($_send_sms) {
					$_sms_case = 24;
					include_once $engine_dir . "/_engine/sms/sms_module.php";
					// 문자내용 한글변수 처리
					$sms_replace['notify_restock_prd'] = $prd['name'];
					$sms_replace['notify_restock_opt'] = $option;
					// 문자발송
					SMS_send_case($_sms_case, $param->buyer_cell);
				}
				unset($_send_sms, $_sms_case);
				// 재입고 알림 신청 문자 발송 End   ===============================

				msg(__lang_notify_restock_success__,"reload","parent");
			} else {
				msg(__lang_notify_restock_fail__,"back","parent");
			}
		}


		break;
	default:
		msg(__lang_notify_restock_invalid_access__,"back","parent");
		break;
}


?>