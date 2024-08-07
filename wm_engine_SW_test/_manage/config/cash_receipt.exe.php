<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  현금영수증/세금계산서 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$_dacom_mid=$_POST['CST_MID'];
	$_dacom_mert_key=$_POST['dacom_mert_key'];

	// 로그 및 mall.conf 설치
	include_once $engine_dir."/_manage/config/cash_receipt_dacom.php";

    /*
     * [현금영수증 사용 가맹 등록/조회 요청 페이지]
     *
     * 파라미터 전달시 POST를 사용하세요
     */

	foreach($_POST as $key => $val) {
		$_POST[$key] = mb_convert_encoding($val, 'euckr', _BASE_CHARSET_);
	}
    $CST_PLATFORM               = $_POST["CST_PLATFORM"];       		//LG텔레콤 결제 서비스 선택(test:테스트, service:서비스)
    $CST_MID                    = $_POST["CST_MID"];            		//상점아이디(LG텔레콤으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                         		//테스트 아이디는 't'를 반드시 제외하고 입력하세요.
    $LGD_MID                    = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;  //상점아이디(자동생성)
    $LGD_TID                	= $_POST["LGD_TID"];			 		//LG텔레콤으로 부터 내려받은 거래번호(LGD_TID)

	$LGD_METHOD   		    	= $_POST["LGD_METHOD"];                //메소드('REG_REQUEST':등록요청, 'REG_RESULT' 등록결과확인)
	$LGD_REG_BUSINESSNUM 		= $_POST["LGD_REG_BUSINESSNUM"];    	//현금영수증 가맹 사업자 등록번호
    $LGD_REG_MERTNAME 			= $_POST["LGD_REG_MERTNAME"];    		//현금영수증 가맹 사업자명
	$LGD_REG_MERTPHONE 			= $_POST["LGD_REG_MERTPHONE"];    		//현금영수증 가맹 사업자 전화번호
    $LGD_REG_CEONAME 			= $_POST["LGD_REG_CEONAME"];    		//현금영수증 가맹 사업자 대표자명
	$LGD_REG_MERTADDRESS 		= $_POST["LGD_REG_MERTADDRESS"];    	//현금영수증 가맹 사업장주소


    require_once($configPath."/XPayClient.php");
    $xpay = new XPayClient($configPath, $CST_PLATFORM);
    $xpay->Init_TX($LGD_MID);
    $xpay->Set("LGD_TXNAME", "CashReceipt");
    $xpay->Set("LGD_METHOD", $LGD_METHOD);
    $xpay->Set("LGD_REG_BUSINESSNUM", $LGD_REG_BUSINESSNUM);
    $xpay->Set("LGD_REG_MERTNAME", $LGD_REG_MERTNAME);
    $xpay->Set("LGD_REG_MERTPHONE", $LGD_REG_MERTPHONE);
    $xpay->Set("LGD_REG_CEONAME", $LGD_REG_CEONAME);
    $xpay->Set("LGD_REG_MERTADDRESS", $LGD_REG_MERTADDRESS);


    /*
     * 1. 현금영수증 사용 가맹 등록/조회 요청 결과처리
     *
     * 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
     */
    if ($xpay->TX()) {
        //1)현금영수증 사업자 등록요청/결과확인  화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
        echo "현금영수증 가맹 사업자 등록요청/결과확인 요청처리가 완료되었습니다. <br>";
        echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
        echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

        echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
        echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<br>";
        echo "사업자 번호 : " . $xpay->Response("LGD_REG_BUSINESSNUM",0) . "<br>";
        echo "사업자명 : " . $xpay->Response("LGD_REG_MERTNAME",0) . "<br>";
        echo "사업자전화번호 : " . $xpay->Response("LGD_REG_MERTPHONE",0) . "<br>";
        echo "대표자명 : " . $xpay->Response("LGD_REG_CEONAME",0) . "<br>";
        echo "사업장 주소 : " . $xpay->Response("LGD_REG_MERTADDRESS",0) . "<br>";
        echo "등록요청일자 : " . $xpay->Response("LGD_REG_REQDATE",0) . "<p>";

		$LGD_RESPCODE=$xpay->Response("LGD_RESPCODE",0);

		if($LGD_RESPCODE == "0000" || $LGD_RESPCODE == "000X"){

			$pdo->query("alter table `$tbl[cash_receipt]` modify `mtrsno` varchar(50) NOT NULL");

			// cfg 변수 설정
			if($_POST['cash_config_update'] == "Y"){

				$_POST['cash_receipt_use']="Y";
				$_POST['cash_r_pg']="dacom";
				$_POST['cash_receipt_auto']=$_POST['cash_receipt_auto'];
				$_POST['cash_receipt_stat']=$_POST['cash_receipt_stat'];
				$_POST['cash_dacom_id']=$_POST['CST_MID'];
				$_POST['cash_dacom_key']=$_POST['dacom_mert_key'];
				$_POST['config_code']="cash_receipt";
				$no_reload_config=1;
				include_once $engine_dir."/_manage/config/config.exe.php";

			}

			$cfg['cash_r_pg']=$cfg['cash_r_pg'] ? $cfg['cash_r_pg'] : "dacom";

			// 2010-11-04 : 위글아이로 로그 날림 - Han
			$we_cash = new WeagleEyeClient($_we, 'receipt');
			$we_cash->queue("register", "wing", $wec->config['account_idx'], $root_url, $cfg['cash_r_pg'], $_POST['cash_receipt_auto'], $_POST['LGD_REG_BUSINESSNUM'], $_POST['LGD_REG_MERTNAME'], $_POST['LGD_REG_MERTPHONE'], $_POST['LGD_REG_CEONAME'], $_POST['LGD_REG_MERTADDRESS'], $now);
			$we_cash->send_clean();

			msg("현금영수증 가맹 등록이 성공 처리되었습니다", "reload", "parent");

		}else{
			msg(addslashes($xpay->Response("LGD_RESPMSG",0)));
		}

    }else {
        //2)API 요청 실패 화면처리
        echo "현금영수증 가맹 사업자 등록요청/결과확인 요청처리가 실패되었습니다. <br>";
        echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
        echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";
		msg("처리실패!");
    }

?>