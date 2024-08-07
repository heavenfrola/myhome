<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  데이콤 현금영수증 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$_dacom_mid=$cfg['cash_dacom_id'];
	$_dacom_mert_key=$cfg['cash_dacom_key'];

	// 로그 및 mall.conf 설치
	include_once $engine_dir."/_manage/config/cash_receipt_dacom.php";

	foreach($curl_fd as $key => $val) {
		$curl_fd[$key] = addslashes(trim($val));
	}

	// 타 상점과의 주문번호 충돌 방지
	if(defined('use_cash_receipt_prefix') == true) {
		$ono = trim(addslashes($curl_fd['LGD_OID']));
		$ord = $pdo->assoc("select date1 from $tbl[order] where ono='$ono'");
		$ono_prefix = $ord['date1'].'_';
	}

	ob_start();
    $CST_PLATFORM               = $curl_fd["CST_PLATFORM"];       		//LG텔레콤 결제 서비스 선택(test:테스트, service:서비스)
    $CST_MID                    = $curl_fd["CST_MID"];            		//상점아이디(LG텔레콤으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                         		//테스트 아이디는 't'를 반드시 제외하고 입력하세요.
    $LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;  //상점아이디(자동생성)
    $LGD_TID                	= $curl_fd["LGD_TID"];			 		//LG텔레콤으로 부터 내려받은 거래번호(LGD_TID)

	$LGD_METHOD   		    	= $curl_fd["LGD_METHOD"];                //메소드('AUTH':승인, 'CANCEL' 취소)
    $LGD_OID                	= $curl_fd["LGD_OID"];		//주문번호(상점정의 유니크한 주문번호를 입력하세요)
    $LGD_PAYTYPE                = $curl_fd["LGD_PAYTYPE"];				//결제수단 코드 (SC0030:계좌이체, SC0040:가상계좌, SC0100:무통장입금 단독)
    $LGD_AMOUNT     		    = $curl_fd["LGD_AMOUNT"];            	//금액("," 를 제외한 금액을 입력하세요)
    $LGD_CASHCARDNUM        	= $curl_fd["LGD_CASHCARDNUM"];           //발급번호(주민등록번호,현금영수증카드번호,휴대폰번호 등등)
    $LGD_CUSTOM_MERTNAME 		= $curl_fd["LGD_CUSTOM_MERTNAME"];    	//상점명
    $LGD_CUSTOM_BUSINESSNUM 	= $curl_fd["LGD_CUSTOM_BUSINESSNUM"];    //사업자등록번호
    $LGD_CUSTOM_MERTPHONE 		= $curl_fd["LGD_CUSTOM_MERTPHONE"];    	//상점 전화번호
    $LGD_CASHRECEIPTUSE     	= $curl_fd["LGD_CASHRECEIPTUSE"];		//현금영수증발급용도('1':소득공제, '2':지출증빙)
    $LGD_PRODUCTINFO        	= $curl_fd["LGD_PRODUCTINFO"];			//상품명
    $LGD_TID        			= $curl_fd["LGD_TID"];					//텔레콤 거래번호

	$configPath 				= $engine_dir."/_engine/cash.dacom"; //LG텔레콤에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

    require_once($configPath."/XPayClient.php");
    $xpay = new XPayClient($configPath, $CST_PLATFORM);
    $xpay->Init_TX($LGD_MID);
    $xpay->Set("LGD_TXNAME", "CashReceipt");
    $xpay->Set("LGD_METHOD", $LGD_METHOD);
    $xpay->Set("LGD_PAYTYPE", $LGD_PAYTYPE);

    if ($LGD_METHOD == "AUTH"){					// 현금영수증 발급 요청
    	$xpay->Set("LGD_OID", $ono_prefix.$LGD_OID);
    	$xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
    	$xpay->Set("LGD_CASHCARDNUM", $LGD_CASHCARDNUM);
    	$xpay->Set("LGD_CUSTOM_MERTNAME", $LGD_CUSTOM_MERTNAME);
    	$xpay->Set("LGD_CUSTOM_BUSINESSNUM", $LGD_CUSTOM_BUSINESSNUM);
    	$xpay->Set("LGD_CUSTOM_MERTPHONE", $LGD_CUSTOM_MERTPHONE);
    	$xpay->Set("LGD_CASHRECEIPTUSE", $LGD_CASHRECEIPTUSE);

		if ($LGD_PAYTYPE == "SC0030"){				//기결제된 계좌이체건 현금영수증 발급요청시 필수
			$xpay->Set("LGD_TID", $LGD_TID);
		}
		else if ($LGD_PAYTYPE == "SC0040"){			//기결제된 가상계좌건 현금영수증 발급요청시 필수
			$xpay->Set("LGD_TID", $LGD_TID);
			$xpay->Set("LGD_SEQNO", "001");
		}
		else {										//무통장입금 단독건 발급요청
			$xpay->Set("LGD_PRODUCTINFO", $LGD_PRODUCTINFO);
    	}
    }else {											// 현금영수증 취소 요청
    	$xpay->Set("LGD_TID", $LGD_TID);

    	if ($LGD_PAYTYPE == "SC0040"){				//가상계좌건 현금영수증 발급취소시 필수
			$xpay->Set("LGD_SEQNO", "001");
    	}
    }


    /*
     * 1. 현금영수증 발급/취소 요청 결과처리
     *
     * 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
     */
    if ($xpay->TX()) {
        //1)현금영수증 발급/취소결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
        echo "현금영수증 발급/취소 요청처리가 완료되었습니다.  <br>";
        echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
        echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

        echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
        echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<br>";
        echo "거래번호 : " . $xpay->Response("LGD_TID",0) . "<p>";
        $keys = $xpay->Response_Names();

		$LGD_RESPCODE=$xpay->Response("LGD_RESPCODE",0);

		if($LGD_RESPCODE == "0000"){
			$stat=($curl_fd['LGD_METHOD'] == 'AUTH') ? 2 : 3;
			$_authno=$xpay->Response("LGD_RESPCODE",0);
			$_mtrsno=$xpay->Response("LGD_TID",0);
			// 2010-07-22 : 실행 날짜 저장 - Han
			$_tsdtime=$xpay->Response("LGD_RESPDATE",0);
			$_tsdtime=$_tsdtime ? strtotime(preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "$1-$2-$3 $4:$5:$6", $_tsdtime)) : "";
			$_result="ok";
		}else{
			$_result="fail";
		}

    }else {
		$_result="fail";
    }

	ob_end_clean();

	$result = json_encode(array('result' => $_result, 'message' => iconv('euc-kr', _BASE_CHARSET_, $xpay->Response_Msg())));
/*
	if($_result != "ok"){
		header('Content-type:application/json; charset=utf-8;');
		exit($result);
	}
*/
	if($_result == "ok") {
		$data = $pdo->assoc("select * from `$tbl[cash_receipt]` where `no`='$cno'");
		$b_num = $LGD_CUSTOM_BUSINESSNUM;
		$_mtrs_q=($_mtrsno) ? ", `mtrsno`='".$_mtrsno."'" : "";
		$_mtrs_q .= ($_tsdtime) ? ", `tsdtime`='".$_tsdtime."'" : "";
		$_mtrs_q .= ($curl_fd['auto'] == "Y") ? ", `mcht_name`='auto'" : "";
		$_mtrs_q .= ($b_num) ? ", `b_num`='".$b_num."'" : "";

		if($curl_fd['cash_admin_no']){
			$admin['admin_id'] = $curl_fd['cash_admin_id'];
			$admin['no'] = $curl_fd['cash_admin_no'];
		}

		$csql="update `$tbl[cash_receipt]` set `stat`='$stat', `authno`='".$_authno."' ".$_mtrs_q." where `no`='$cno' order by no desc limit 1";
		$r=$pdo->query($csql);
		if($r){
            cashReceiptLog(array(
                'cno' => $data['no'],
                'ono' => $data['ono'],
                'stat' => $stat,
                'ori_stat' => $data['stat'],
                'admin_id' => $admin['admin_id'],
                'system' => ($curl_fd['auto'] == 'Y') ? 'Y' : 'N'
            ));

			// 2010-11-08 : 위글아이로 실행 로그 보냄 - Han
			$w_receipt = new weagleEyeClient($_we, 'receipt');
			$_log_data=array();
			$_log_data['engine']='wing';
			$_log_data['account_idx']=$wec->config['account_idx'];
			$_log_data['domain']=$root_url;
			$_log_data['pg']=$cfg['cash_r_pg'];
			$_log_data['ono']=$data['ono'];
			$_log_data['stat']=$stat;
			$_log_data['price']=$LGD_AMOUNT;
			$_log_data['cash_reg_num']=$LGD_CASHCARDNUM;
			$_log_data['b_num']=$b_num;
			$_log_data['prod_name']=$data['prod_name'];
			$_log_data['cons_name']=$data['cons_name'];
			$_log_data['cons_tel']=$data['cons_tel'];
			$_log_data['cons_email']=$data['cons_email'];
			$_log_data['mtrsno']=$_mtrsno;
			$_log_data['reg_date']=$now;
			$w_receipt->call('receipt_log', $_log_data);
		}
	}
//	exit($result);

?>