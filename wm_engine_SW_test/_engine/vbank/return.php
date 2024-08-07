<?PHP

	include $engine_dir."/_engine/include/common.lib.php";

    /* ============================================================================== */
    /* =   PAGE : 가상계좌 입금 통보 페이지                                          = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2005   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   01. 가상계좌 입금 통보 페이지 설명(필독!!)                                 = */
    /* = -------------------------------------------------------------------------- = */
    /* =     가상계좌의 경우, 결제자가 최종적으로 부여된 가상계좌에 대해 입금을         = */
    /* =     해야 결제가 완료됩니다. 결제자가 입금하는 시각은 일정치 않기 때문에        = */
    /* =     (결제가 이루어지면 가상계좌번호를 부여받아서 최종적으로 해당 계좌번호에    = */
    /* =     결제자가 직접 입금을 해야 결제가 완료됨) 결제자가 입금을 하면, 해당        = */
    /* =     결제건에 대해 KCP 에서 가맹점측으로 입금 결과를 전송하고, 전송이 정상적    = */
    /* =     으로 이루어지면 모든 프로세스가 완료됩니다.                               = */
    /* =     그러므로 가맹점측은 입금 결과를 전송받는 페이지를 마련해 놓아야 합니다.    = */
    /* =     그리고 현재의 페이지를 업체에 맞게 수정하신 후, KCP 관리자 페이지에        = */
    /* =     등록해 주시기 바랍니다. 등록 방법은 연동 매뉴얼을 참고하시기 바랍니다.     = */
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 공통 통보 데이터 받기                                                = */
    /* = -------------------------------------------------------------------------- = */
    $site_cd      = $_POST [ "site_cd"  ];                 // 사이트 코드
    $tno          = $_POST [ "tno"      ];                 // KCP 거래번호
    $order_no     = $_POST [ "order_no" ];                 // 주문번호
    $tx_cd        = $_POST [ "tx_cd"    ];                 // 업무처리 구분 코드
    $tx_tm        = $_POST [ "tx_tm"    ];                 // 업무처리 완료 시간
    /* = -------------------------------------------------------------------------- = */
    $ipgm_name    = "";                                    // 주문자명
    $remitter     = "";                                    // 입금자명
    $ipgm_mnyx    = "";                                    // 입금 금액
    $bank_code    = "";                                    // 은행코드
    $account      = "";                                    // 가상계좌 입금계좌번호
    $op_cd        = "";                                    // 처리구분 코드
    $noti_id      = "";                                    // 통보 아이디
    /* = -------------------------------------------------------------------------- = */
    $refund_nm    = "";                                    // 환불계좌주명
    $refund_mny   = "";                                    // 환불금액
    $bank_code    = "";                                    // 은행코드
    /* = -------------------------------------------------------------------------- = */
    $st_cd        = "";                                    // 구매확인 코드
    $can_msg      = "";                                    // 구매취소 사유
    /* = -------------------------------------------------------------------------- = */
    $waybill_no   = "";                                    // 운송장 번호
    $waybill_corp = "";                                    // 택배 업체명




    /* = -------------------------------------------------------------------------- = */
    /* =   02-1. 가상계좌 입금 통보 데이터 받기                                     = */
    /* = -------------------------------------------------------------------------- = */
    if ( $tx_cd == "TX00" )
    {
        $ipgm_name = $_POST[ "ipgm_name" ];                // 주문자명
        $remitter  = $_POST[ "remitter"  ];                // 입금자명
        $ipgm_mnyx = $_POST[ "ipgm_mnyx" ];                // 입금 금액
        $bank_code = $_POST[ "bank_code" ];                // 은행코드
        $account   = $_POST[ "account"   ];                // 가상계좌 입금계좌번호
        $op_cd     = $_POST[ "op_cd"     ];                // 처리구분 코드
        $noti_id   = $_POST[ "noti_id"   ];                // 통보 아이디
    }
    /* = -------------------------------------------------------------------------- = */
    /* =   02-2. 가상계좌 환불 통보 데이터 받기                                     = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX01" )
    {
        $refund_nm  = $_POST[ "refund_nm"  ];              // 환불계좌주명
        $refund_mny = $_POST[ "refund_mny" ];              // 환불금액
        $bank_code  = $_POST[ "bank_code"  ];              // 은행코드
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   02-3. 구매확인/구매취소 통보 데이터 받기                                 = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX02" )
    {
        $st_cd = $_POST[ "st_cd" ];                        // 구매확인 코드

        if ( $st_cd == "N" )                               // 구매확인 상태가 구매취소인 경우
        {
            $can_msg = $_POST[ "can_msg" ];                // 구매취소 사유
        }
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   02-4. 배송시작 통보 데이터 받기                                          = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX03" )
    {
        $waybill_no   = $_POST[ "waybill_no"   ];          // 운송장 번호
        $waybill_corp = $_POST[ "waybill_corp" ];          // 택배 업체명
    }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 공통 통보 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.      = */
    /* = -------------------------------------------------------------------------- = */
    /* =   통보 결과를 DB 작업 하는 과정에서 정상적으로 통보된 건에 대해 DB 작업을  = */
    /* =   실패하여 DB update 가 완료되지 않은 경우, 결과를 재통보 받을 수 있는     = */
    /* =   프로세스가 구성되어 있습니다. 소스에서 result 라는 Form 값을 생성 하신   = */
    /* =   후, DB 작업이 성공 한 경우, result 의 값을 "0000" 로 세팅해 주시고,      = */
    /* =   DB 작업이 실패 한 경우, result 의 값을 "0000" 이외의 값으로 세팅해 주시  = */
    /* =   기 바랍니다. result 값이 "0000" 이 아닌 경우에는 재통보를 받게 됩니다.   = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 가상계좌 입금 통보 데이터 DB 처리 작업 부분                        = */
    /* = -------------------------------------------------------------------------- = */

	$tres="0000";
	// 가상 계좌 기등록 정보
	$data=get_info($tbl[vbank],"tno",$tno);
	makePGLog($data['wm_ono'], 'kcp Vbank');
	$ori_stat = $pdo->row("select `stat` from `$tbl[order]` where `ono`='$data[wm_ono]'");

	$ipgm_name = mb_convert_encoding($ipgm_name, _BASE_CHARSET_, 'euc-kr');
	$remitter = mb_convert_encoding($remitter, _BASE_CHARSET_, 'euc-kr');

    if ( $tx_cd == "TX00" )
    {

		if(!$data[no] || $data[wm_pricec]>$ipgm_mnyx) {
			$tres="0001";
		}
		else if($ori_stat == 1){
			$sql="update `$tbl[vbank]` set `stat`='3', `ipgm_name`='$ipgm_name',`ipgm_mnyx`='$ipgm_mnyx',`ipgm_time`='$ipgm_time',`bank_code`='$bank_code',`op_cd`='$op_cd',`noti_id`='$noti_id',`remitter`='$remitter' where `no`='$data[no]'";
			$pdo->query($sql);

			$ord=get_info($tbl[order],"ono",$data[wm_ono]);
			// $ipgm_time = 20050525112233
			$t[0]=substr($ipgm_time,0,4);
			$t[1]=substr($ipgm_time,4,2);
			$t[2]=substr($ipgm_time,6,2);
			$t[3]=substr($ipgm_time,8,2);
			$t[4]=substr($ipgm_time,10,2);
			$t[5]=substr($ipgm_time,12,2);
			$date2=mktime($t[3],$t[4],$t[5],$t[1],$t[2],$t[0]);

			$sql="update `$tbl[order]` set `date2`='$date2', `stat`='2', `bank_name`='$remitter' where `no`='$ord[no]'";

			// 2011-11-21 윙포스 재고처리 추가 by zardsama
			include_once $engine_dir.'/_engine/include/wingPos.lib.php';
			$erp_auto_input = 'Y'; // 재고가 모자랄경우 재고확인상태로 변경
			if(!orderStock($ord['ono'], 1, 2)) { // 재고가 남아있을경우
				if($pdo->query($sql)){
					$pdo->query("update `$tbl[order]` set `stat`=2, `date2`='$now' where ono='$ord[ono]'");
					ordStatLogw($ord['ono'], 2, "Y");
					$add_q="";
					if($cfg[repay_part] == "Y") $add_q=" and `repay_date`=0";
					$sql="update `$tbl[order_product]` set `stat`='2' where `ono`='$ord[ono]'".$add_q;
					$pdo->query($sql);

					if(is_object($erpListener)) {
						$erpListener->setOrder($ord['ono']);
					}
				}
			}

			ordChgPart($data['wm_ono']);

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

			$pdo->query("update $tbl[order_payment] set stat=2 where ono='$ord[ono]' and type=0");
		}
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-2. 가상계좌 환불 통보 데이터 DB 처리 작업 부분                        = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX01" )
    {
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-3. 구매확인/구매취소 통보 데이터 DB 처리 작업 부분                    = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX02" )
    {
		//$sql="update `$tbl[vbank]` set `stat`='4', `st_cd`='$st_cd', `can_msg`='$can_msg' where `no`='$data[no]'";
		//$pdo->query($sql);
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-4. 배송시작 통보 데이터 DB 처리 작업 부분                             = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX03" )
    {


    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-5. 정산보류 통보 데이터 DB 처리 작업 부분                             = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX04" )
    {



    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-6. 즉시취소 통보 데이터 DB 처리 작업 부분                             = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX05" )
    {
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-7. 취소 통보 데이터 DB 처리 작업 부분                                 = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX06" )
    {
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-7. 발급계좌해지 통보 데이터 DB 처리 작업 부분                         = */
    /* = -------------------------------------------------------------------------- = */
    else if ( $tx_cd == "TX07" )
    {
    }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. result 값 세팅 하기                                                  = */
    /* ============================================================================== */

?>
<html><body><form><input type="hidden" name="result" value="<?=$tres?>"></form></body></html>