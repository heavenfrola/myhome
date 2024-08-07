<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	@extract($_GET);
	@extract($_POST);
	@extract($_SERVER);

	// Return
	$PayMethod      = $PayMethod;           //지불수단
	$M_ID           = $MID;                 //상점ID
	$MallUserID     = $MallUserID;          //회원사 ID
	$Amt            = $Amt;                 //금액
	$name           = $name;                //구매자명
	$GoodsName      = $GoodsName;           //상품명
	$TID            = $TID;                 //거래번호
	$MOID           = $MOID;                //주문번호
	$AuthDate       = $AuthDate;            //입금일시 (yyMMddHHmmss)
	$ResultCode     = $ResultCode;          //결과코드 ('4110' 경우 입금통보)
	$ResultMsg      = $ResultMsg;           //결과메시지
	$VbankNum       = $VbankNum;            //가상계좌번호
	$FnCd           = $FnCd;                //가상계좌 은행코드
	$VbankName      = $VbankName;           //가상계좌 은행명
	$VbankInputName = $VbankInputName;      //입금자 명
	$CancelDate     = $CancelDate;          //취소일시

	//가상계좌채번시 현금영수증 자동발급신청이 되었을경우 전달되며
	//RcptTID 에 값이 있는경우만 발급처리 됨
	$RcptTID        = $RcptTID;             //현금영수증 거래번호
	$RcptType       = $RcptType;            //현금 영수증 구분(0:미발행, 1:소득공제용, 2:지출증빙용)
	$RcptAuthCode   = $RcptAuthCode;        //현금영수증 승인번호

	makePGLog($MOID, 'nicepay vbank start', print_r($_REQUEST, true));

	if($ResultCode != '4110') exit("FAIL\n");

	//가맹점 DB처리
	$ono = addslashes($MOID);
	$ord = $pdo->assoc("select ono, stat, pay_type, buyer_name, buyer_cell, pay_prc from {$tbl['order']} where ono='$ono'");
	$card = $pdo->assoc("select tno from {$tbl['vbank']} where wm_ono='$ono'");

	if($ord['stat'] != 1) exit("OK\n");
	if($card['tno'] != $TID) exit("FAIL\n");

	$erp_auto_input = 'Y'; // 재고가 모자랄 경우 재고 확인 상태로 변경
	if(orderStock($ono, 1, 2)) exit("OK\n");

	// 주문 상태 변경
	$pdo->query("update {$tbl['vbank']} set stat='2' where wm_ono='$ono'");
	$pdo->query("update {$tbl['order_product']} set stat='2' where ono='$ono'");
	ordChgPart($ono);
	ordStatLogw($ono, 2, 'Y');

	// 입금확인 SMS
	include_once $engine_dir.'/_engine/sms/sms_module.php';
	$sms_replace['buyer_name'] = $ord['buyer_name'];
	$sms_replace['ono'] = $ord['ono'];
	$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
	SMS_send_case(3, $ord['buyer_cell']);
	SMS_send_case(18);

	if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
		partnerSmsSend($ord['ono'], 18);
	}

	makePGLog($ono, 'nicepay vbank end');

	exit("OK\n");

?>