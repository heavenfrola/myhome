<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

    $LGD_RESPCODE            = $_POST["LGD_RESPCODE"];             // 응답코드: 0000(성공) 그외 실패
    $LGD_RESPMSG             = $_POST["LGD_RESPMSG"];              // 응답메세지
    $LGD_MID                 = $_POST["LGD_MID"];                  // 상점아이디
    $LGD_OID                 = $_POST["LGD_OID"];                  // 주문번호
    $LGD_AMOUNT              = $_POST["LGD_AMOUNT"];               // 거래금액
    $LGD_TID                 = $_POST["LGD_TID"];                  // LG유플러스에서 부여한 거래번호
    $LGD_PAYTYPE             = $_POST["LGD_PAYTYPE"];              // 결제수단코드
    $LGD_PAYDATE             = $_POST["LGD_PAYDATE"];              // 거래일시(승인일시/이체일시)
    $LGD_HASHDATA            = $_POST["LGD_HASHDATA"];             // 해쉬값
    $LGD_FINANCECODE         = $_POST["LGD_FINANCECODE"];          // 결제기관코드(은행코드)
    $LGD_FINANCENAME         = $_POST["LGD_FINANCENAME"];          // 결제기관이름(은행이름)
    $LGD_ESCROWYN            = $_POST["LGD_ESCROWYN"];             // 에스크로 적용여부
    $LGD_TIMESTAMP           = $_POST["LGD_TIMESTAMP"];            // 타임스탬프
    $LGD_ACCOUNTNUM          = $_POST["LGD_ACCOUNTNUM"];           // 계좌번호(무통장입금)
    $LGD_CASTAMOUNT          = $_POST["LGD_CASTAMOUNT"];           // 입금총액(무통장입금)
    $LGD_CASCAMOUNT          = $_POST["LGD_CASCAMOUNT"];           // 현입금액(무통장입금)
    $LGD_CASFLAG             = $_POST["LGD_CASFLAG"];              // 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
    $LGD_CASSEQNO            = $_POST["LGD_CASSEQNO"];             // 입금순서(무통장입금)
    $LGD_CASHRECEIPTNUM      = $_POST["LGD_CASHRECEIPTNUM"];       // 현금영수증 승인번호
    $LGD_CASHRECEIPTSELFYN   = $_POST["LGD_CASHRECEIPTSELFYN"];    // 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용
    $LGD_CASHRECEIPTKIND     = $_POST["LGD_CASHRECEIPTKIND"];      // 현금영수증 종류 0: 소득공제용 , 1: 지출증빙용
	$LGD_PAYER     			 = $_POST["LGD_PAYER"];      			// 입금자명

    /*
     * 구매정보
     */
    $LGD_BUYER               = $_POST["LGD_BUYER"];                // 구매자
    $LGD_PRODUCTINFO         = $_POST["LGD_PRODUCTINFO"];          // 상품명
    $LGD_BUYERID             = $_POST["LGD_BUYERID"];              // 구매자 ID
    $LGD_BUYERADDRESS        = $_POST["LGD_BUYERADDRESS"];         // 구매자 주소
    $LGD_BUYERPHONE          = $_POST["LGD_BUYERPHONE"];           // 구매자 전화번호
    $LGD_BUYEREMAIL          = $_POST["LGD_BUYEREMAIL"];           // 구매자 이메일
    $LGD_BUYERSSN            = $_POST["LGD_BUYERSSN"];             // 구매자 주민번호
    $LGD_PRODUCTCODE         = $_POST["LGD_PRODUCTCODE"];          // 상품코드
    $LGD_RECEIVER            = $_POST["LGD_RECEIVER"];             // 수취인
    $LGD_RECEIVERPHONE       = $_POST["LGD_RECEIVERPHONE"];        // 수취인 전화번호
    $LGD_DELIVERYINFO        = $_POST["LGD_DELIVERYINFO"];         // 배송지

	$sno = addslashes($LGD_OID);
	if(!$sno) exit('필수값이 없습니다.');
	$ordsub = $pdo->assoc("select sno, pay_type, pay_prc, date1, stat from `$tbl[subscription]` where sno='$sno'");
	$ordsub['pay_prc'] = parsePrice($ordsub['pay_prc']);
	$card_tbl = ($ordsub['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ordsub[sno]'");
	if(!$card['no']) exit('결제데이터가 없습니다.');

	$LGD_MERTKEY = $cfg['card_dacom_key'];
    $LGD_HASHDATA2 = md5($LGD_MID.$LGD_OID.$ordsub['pay_prc'].$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY);

    $resultMSG = '';

    if($LGD_HASHDATA2 == $LGD_HASHDATA) { //해쉬값 검증이 성공이면
        if("0000" == $LGD_RESPCODE){ //결제가 성공이면
        	if("R" == $LGD_CASFLAG) {
                $resultMSG = "OK";
        	} else if("I" == $LGD_CASFLAG) {
				if($ordsub['stat'] != 1) exit('OK');

				$pdo->query("update `$tbl[vbank]` set `stat`='2' where `wm_ono`='$sno'");

			//	$erp_auto_input = 'Y'; // 재고가 모자랄경우 재고확인상태로 변경
			//	if(orderStock($sno, 1, 2)) exit('OK');

				$pdo->query("update `$tbl[subscription]` set `stat`='2', `stat2`='@2@', `date2`='$now' where `sno`='$sno'");
				$pdo->query("update `$tbl[subscription_product]` set `stat`='2' where `sno`='$sno'");
				$pdo->query("update `$tbl[subscription_booking]` set `stat`='2' where `sno`='$sno'");
				ordStatLogw($sno, 2, 'Y');
				makeOrderLog($sno);

				// 입금확인 SMS
				include_once $engine_dir.'/_engine/sms/sms_module.php';
				$sms_replace['buyer_name'] = $ordsub['buyer_name'];
				$sms_replace['ono'] = $ordsub['sno'];
				$sms_replace['pay_prc'] = number_format($ordsub['pay_prc']);
				SMS_send_case(3, $ordsub['buyer_cell']);
				SMS_send_case(18);

            	$resultMSG = "OK";
        	}else if("C" == $LGD_CASFLAG) {
				if($ordsub['stat'] != 1) exit('OK');

				$card = $pdo->assoc("select * from `$tbl[vbank]` where `wm_ono`='$sno'");
				if(!$card['no']) exit("잘못된 주문 정보입니다");

				$pdo->query("update `$tbl[subscription]` set `stat` = '12' where `sno` = '$sno'");
				$pdo->query("update `$tbl[subscription_product]` set `stat` = '12' where `sno` = '$sno'");
				$pdo->query("update `$tbl[subscription_booking]` set `stat` = '12' where `sno` = '$sno'");
				ordStatLogw($sno, 11, 'Y');
				makeOrderLog($sno);

            	$resultMSG = "OK";
        	}
        } else {
            $resultMSG = "OK";
        }
    } else {
        $resultMSG = "결제결과 상점 DB처리(LGD_CASNOTEURL) 해쉬값 검증이 실패하였습니다.";
    }

    exit($resultMSG);

?>