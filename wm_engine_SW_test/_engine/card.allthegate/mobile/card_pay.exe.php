<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 모바일 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	require_once $engine_dir.'/_engine/card.allthegate/mobile/lib/AGSMobile.php';

	$tracking_id = $_REQUEST["tracking_id"];
	$transaction = $_REQUEST["transaction"];
	$store_id = $cfg['mobile_allthegate_StoreId'];

	$agsMobile = new AGSMobile($store_id,$tracking_id,$transaction);
	$agsMobile->setLogging(false); //true : 로그기록, false : 로그기록안함.

	// 전송 데이터
	$info = $agsMobile->getTrackingInfo();

	// 결과 데이터
	$ret = $agsMobile->approve();
	$data = $ret['data'];

	$res_cd = $ret['status'];
	$res_msg = $ret['message'];
	$ono = $data["OrdNo"];
	$rApprNo = $data['AdmNo'];
	$rAppTm = $data['AdmTime'];
	$rDealNo = $data['DealNo'];
	$ES_SENDNO = $data['EscrowSendNo'];
	$rCardCd = $data['CardCd'];
	$card_name = mb_convert_encoding($data['CardNm'], _BASE_CHARSET_, 'utf-8');
	$quota = $data['PartialMm'];
	$pay_method	= $data['AuthTy'];
	$SubTy = $data['SubTy'];
	$rVirNo = $data['VirtualNo'];
	$bank_code = $data['BankCode'];
	$Amt = $data['Amt'];

	define('__pg_card_pay.exe__', $ono);
	foreach($ret as $key => $val) {
		$logs .= "[ret] $key => $val\n";
	}
	foreach($data as $key => $val) {
		$logs .= "[data] $key => $val\n";
	}
	makePGLog($ono, 'allthegate mobile Start', $logs);

	// 주문데이터 검증
	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ord[ono]'");
	if(!$ord['ono']) {
		$res_cd = 'error';
		$res_msg = '존재하지 않는 주문번호입니다.';
		$cancelRet = $agsMobile->forceCancel();
		$ret['status'] = 'error';
	}
	if(!$card['no']) {
		$res_cd = 'error';
		$res_msg = '결제 데이터가 없습니다.';
		$cancelRet = $agsMobile->forceCancel();
		$ret['status'] = 'error';
	}

	if($ret['status'] == "ok") {
		if($ord['pay_type'] == 4) {
			$asql .= ", `bank_code`='$bank_code', `account`='$rVirNo', `bankname`='', `ipgm_time`='$rAppTm', `op_cd`='$rApprNo'";
			$rDealNo = $ES_SENDNO;
		} else {
			$asql = ", `card_cd`='$rCardCd' ,`card_name`='$card_name', `app_time`='$rAppTm' ,`app_no`='$rApprNo',`quota`='$quota'";
		}
		$pdo->query("update `$card_tbl` set`stat`='2', `res_msg`='$res_msg', `good_mny`='$Amt', `ordr_idxx`='$ono' ,`tno`='$rDealNo', `use_pay_method`='$pay_method', `env_info`='$SubTy' $asql where `wm_ono`='$ono'");

		makePGLog($ono, 'allthegate mobile success');

		include_once $engine_dir.'/_engine/order/order2.exe.php';
		exit;
	} else {
		$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
		$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
		$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='$res_msg' where wm_ono='$ono'");

		makePGLog($ono, 'allthegate mobile faild');

		msg('결제요청이 실패되었습니다.'.php2java($res_msg));
	}

?>