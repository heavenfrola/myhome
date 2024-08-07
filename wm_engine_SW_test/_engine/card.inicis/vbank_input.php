<?php

	$urlfix = 'Y';
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";

	@extract($_GET);
	@extract($_POST);
	@extract($_SERVER);

	$INIpayHome = file_exists($root_dir.'/_data/INIpay41') ? $root_dir.'/_data/INIpay41' : $root_dir.'/INIpay41';

	$TEMP_IP = getenv("REMOTE_ADDR");
	$PG_IP  = substr($TEMP_IP,0, 10);

	if($no_oid) {
		makePGLog($no_oid, 'INIPay Vbank input');
	}

	if($PG_IP == "203.238.37" || $PG_IP == "210.98.138" || $PG_IP == '39.115.212' || $PG_IP == '183.109.71') {  //PG에서 보냈는지 IP로 체크
		$msg_id = $msg_id;             //메세지 타입
		$no_tid = $no_tid;             //거래번호
		$no_oid = $no_oid;             //상점 주문번호
		$id_merchant = $id_merchant;   //상점 아이디
		$cd_bank = $cd_bank;           //거래 발생 기관 코드
		$cd_deal = $cd_deal;           //취급 기관 코드
		$dt_trans = $dt_trans;         //거래 일자
		$tm_trans = $tm_trans;         //거래 시간
		$no_msgseq = $no_msgseq;       //전문 일련 번호
		$cd_joinorg = $cd_joinorg;     //제휴 기관 코드

		$dt_transbase = $dt_transbase; //거래 기준 일자
		$no_transeq = $no_transeq;     //거래 일련 번호
		$type_msg = $type_msg;         //거래 구분 코드
		$cl_close = $cl_close;         //마감 구분코드
		$cl_kor = $cl_kor;             //한글 구분 코드
		$no_msgmanage = $no_msgmanage; //전문 관리 번호
		$no_vacct = $no_vacct;         //가상계좌번호
		$amt_input = $amt_input;       //입금금액
		$amt_check = $amt_check;       //미결제 타점권 금액
		$nm_inputbank = $nm_inputbank; //입금 금융기관명
		$nm_input = $nm_input;         //입금 의뢰인
		$dt_inputstd = $dt_inputstd;   //입금 기준 일자
		$dt_calculstd = $dt_calculstd; //정산 기준 일자
		$flg_close = $flg_close;       //마감 전화

		//가상계좌채번시 현금영수증 자동발급신청시에만 전달
		$dt_cshr      = $dt_cshr;       //현금영수증 발급일자
		$tm_cshr      = $tm_cshr;       //현금영수증 발급시간
		$no_cshr_appl = $no_cshr_appl;  //현금영수증 발급번호
		$no_cshr_tid  = $no_cshr_tid;   //현금영수증 발급TID

		$stat = $pdo->row("select stat from `$tbl[order]` where `ono` = '$no_oid'");
		if($stat != '1') {
			echo('OK');
			exit;
		}

		$logfile = fopen( $INIpayHome . "/log/result.log", "a+" );
		fwrite( $logfile,"************************************************");
		fwrite( $logfile,"ID_MERCHANT : ".$id_merchant."\r\n");
		fwrite( $logfile,"NO_TID : ".$no_tid."\r\n");
		fwrite( $logfile,"NO_OID : ".$no_oid."\r\n");
		fwrite( $logfile,"NO_VACCT : ".$no_vacct."\r\n");
		fwrite( $logfile,"AMT_INPUT : ".$amt_input."\r\n");
		fwrite( $logfile,"NM_INPUTBANK : ".$nm_inputbank."\r\n");
		fwrite( $logfile,"NM_INPUT : ".$nm_input."\r\n");
		fwrite( $logfile,"************************************************");
		fclose( $logfile );

		$r = $pdo->query("update `$tbl[order_product]` set `stat`='2', `repay_date`=0 where `ono`='$no_oid' ".$add_q);
		if($r) {
			ordChgPart($no_oid);
			ordStatLogw($no_oid, 2, 'Y');

			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$sms_replace['buyer_name'] = $ord['buyer_name'];
			$sms_replace['ono'] = $ord['ono'];
			$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
			SMS_send_case(3, $ord['buyer_cell']);
			SMS_send_case(18);

			if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
				partnerSmsSend($ord['ono'], 18);
			}
		}

		makePGLog($no_oid, 'INIPay Vbank input Finish');
		echo('OK');
	} else {
		msg("정상적인 접근이 아닙니다.");
	}

?>