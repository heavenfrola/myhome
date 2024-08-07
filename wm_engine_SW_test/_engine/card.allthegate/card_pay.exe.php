<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 PG결제 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	define('__pg_card_pay.exe__', $_POST['OrdNo']);
	makePGLog($_POST['OrdNo'], 'allthegate Start');

	foreach($_POST as $key => $row) $_POST[$key] = iconv("UTF-8", "EUC-KR", $row);

	require_once $engine_dir."/_engine/card.allthegate/AGSLib41.php";
	$agspay = new agspay40;

	javac("
		try {
			var openwin = window.open('AGS_progress.html','popup','width=300,height=160');
			openwin.close();
		} catch(ex) {}
	");

	$_card_name = array(
		'0100' => 'BC',		'0206' => '씨티',	'0200' => '국민',	'0301' => '제주',	'0300' => '외환',
		'0302' => '광주',	'0400' => '삼성',	'0303' => '전북',	'0500' => '신한',	'0305' => '산은',
		'0800' => '현대',	'0310' => '하나SK',	'0900' => '롯데',	'1000' => 'VISA',	'0201' => 'NH',
		'1100' => 'MASTER',	'0202' => '수협',	'0700' => 'JCB'
	);

	$agspay->SetValue("AgsPayHome",$root_dir.'/_data/card.allthegate');			//올더게이트 결제설치 디렉토리 (상점에 맞게 수정)
	$agspay->SetValue("store_propPath", $engine_dir."/_engine/card.allthegate/store_config.ini");
	$agspay->SetValue("ags_propPath", $engine_dir."/_engine/card.allthegate/ags_config.ini");

	$paramValues = array(trim($_POST["StoreId"]), trim($_POST["OrdNo"]), trim($_POST["Amt"]));
	$whereClause = null;
	$compareResult = $agspay->compareValue($paramValues, $whereClause);

	$agspay->SetValue("StoreId",trim($cfg['allthegate_StoreId']));		//상점아이디
	$agspay->SetValue("log","false");							//true : 로그기록, false : 로그기록안함.
	$agspay->SetValue("logLevel","INFO");						//로그레벨 : DEBUG, INFO, WARN, ERROR, FATAL (해당 레벨이상의 로그만 기록됨)
	$agspay->SetValue("UseNetCancel","true");					//true : 망취소 사용. false: 망취소 미사용
	$agspay->SetValue("Type", "Pay");							//고정값(수정불가)
	$agspay->SetValue("RecvLen", 7);							//수신 데이터(길이) 체크 에러시 6 또는 7 설정.

	$agspay->SetValue("AuthTy",trim($_POST["AuthTy"]));			//결제형태
	$agspay->SetValue("SubTy",trim($_POST["SubTy"]));			//서브결제형태
	$agspay->SetValue("OrdNo",trim($_POST["OrdNo"]));			//주문번호

	// 결제금액 변조 체크
	$pre_ono = addslashes($_POST['OrdNo']);
	$ord = $pdo->assoc("select pay_prc, pay_type from $tbl[order] where ono='$pre_ono'");
	if($_POST['Amt'] != parsePrice($ord['pay_prc'])) {
		$card_tbl = ($_POST["AuthTy"] == 'virtual') ? $tbl['vbank'] : $tbl['card'];
		$pdo->query("update `$card_tbl` set stat=3, res_msg='결제금액변조' where wm_ono='$pre_ono'");
		$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$pre_ono' and stat != 2");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$pre_ono' and stat != 2");

		msg('결제금액이 상이합니다. 다시 결제해 주세요.');
	}

	$agspay->SetValue("Amt",trim($_POST["Amt"]));				//금액
	$agspay->SetValue("UserEmail",$agspay -> makeUserEmail(trim($_POST["UserEmail"]), $compareResult, $whereClause));
	$agspay->SetValue("ProdNm",trim($_POST["ProdNm"]));			//상품명

	/*신용카드&가상계좌사용*/
	$agspay->SetValue("MallUrl",trim($_POST["MallUrl"]));		//MallUrl(무통장입금) - 상점 도메인 가상계좌추가
	$agspay->SetValue("UserId",trim($_POST["UserId"]));			//회원아이디


	/*신용카드사용*/
	$agspay->SetValue("OrdNm",trim($_POST["OrdNm"]));			//주문자명
	$agspay->SetValue("OrdPhone",trim($_POST["OrdPhone"]));		//주문자연락처
	$agspay->SetValue("OrdAddr",trim($_POST["OrdAddr"]));		//주문자주소 가상계좌추가
	$agspay->SetValue("RcpNm",trim($_POST["RcpNm"]));			//수신자명
	$agspay->SetValue("RcpPhone",trim($_POST["RcpPhone"]));		//수신자연락처
	$agspay->SetValue("DlvAddr",trim($_POST["DlvAddr"]));		//배송지주소
	$agspay->SetValue("Remark",trim($_POST["Remark"]));			//비고
	$agspay->SetValue("DeviId",trim($_POST["DeviId"]));			//단말기아이디
	$agspay->SetValue("AuthYn",trim($_POST["AuthYn"]));			//인증여부
	$agspay->SetValue("Instmt",trim($_POST["Instmt"]));			//할부개월수
	$agspay->SetValue("UserIp",$_SERVER["REMOTE_ADDR"]);		//회원 IP

	/*신용카드(ISP)*/
	$agspay->SetValue("partial_mm",trim($_POST["partial_mm"]));		//일반할부기간
	$agspay->SetValue("noIntMonth",trim($_POST["noIntMonth"]));		//무이자할부기간
	$agspay->SetValue("KVP_CURRENCY",trim($_POST["KVP_CURRENCY"]));	//KVP_통화코드
	$agspay->SetValue("KVP_CARDCODE",trim($_POST["KVP_CARDCODE"]));	//KVP_카드사코드
	$agspay->SetValue("KVP_SESSIONKEY",$_POST["KVP_SESSIONKEY"]);	//KVP_SESSIONKEY
	$agspay->SetValue("KVP_ENCDATA",$_POST["KVP_ENCDATA"]);			//KVP_ENCDATA
	$agspay->SetValue("KVP_CONAME",trim($_POST["KVP_CONAME"]));		//KVP_카드명
	$agspay->SetValue("KVP_NOINT",trim($_POST["KVP_NOINT"]));		//KVP_무이자=1 일반=0
	$agspay->SetValue("KVP_QUOTA",trim($_POST["KVP_QUOTA"]));		//KVP_할부개월

	/*신용카드(안심)*/
	$agspay->SetValue("CardNo",trim($_POST["CardNo"]));			//카드번호
	$agspay->SetValue("MPI_CAVV",$_POST["MPI_CAVV"]);			//MPI_CAVV
	$agspay->SetValue("MPI_ECI",$_POST["MPI_ECI"]);				//MPI_ECI
	$agspay->SetValue("MPI_MD64",$_POST["MPI_MD64"]);			//MPI_MD64

	/*신용카드(일반)*/
	$agspay->SetValue("ExpMon",trim($_POST["ExpMon"]));				//유효기간(월)
	$agspay->SetValue("ExpYear",trim($_POST["ExpYear"]));			//유효기간(년)
	$agspay->SetValue("Passwd",trim($_POST["Passwd"]));				//비밀번호
	$agspay->SetValue("SocId",trim($_POST["SocId"]));				//주민등록번호/사업자등록번호

	/*계좌이체사용*/
	$agspay->SetValue("ICHE_OUTBANKNAME",trim($_POST["ICHE_OUTBANKNAME"]));		//이체은행명
	$agspay->SetValue("ICHE_OUTACCTNO",trim($_POST["ICHE_OUTACCTNO"]));			//이체계좌번호
	$agspay->SetValue("ICHE_OUTBANKMASTER",trim($_POST["ICHE_OUTBANKMASTER"]));	//이체계좌소유주
	$agspay->SetValue("ICHE_AMOUNT",trim($_POST["ICHE_AMOUNT"]));				//이체금액

	/*핸드폰사용*/
	$agspay->SetValue("HP_SERVERINFO",trim($_POST["HP_SERVERINFO"]));	//SERVER_INFO(핸드폰결제)
	$agspay->SetValue("HP_HANDPHONE",trim($_POST["HP_HANDPHONE"]));		//HANDPHONE(핸드폰결제)
	$agspay->SetValue("HP_COMPANY",trim($_POST["HP_COMPANY"]));			//COMPANY(핸드폰결제)
	$agspay->SetValue("HP_ID",trim($_POST["HP_ID"]));					//HP_ID(핸드폰결제)
	$agspay->SetValue("HP_SUBID",trim($_POST["HP_SUBID"]));				//HP_SUBID(핸드폰결제)
	$agspay->SetValue("HP_UNITType",trim($_POST["HP_UNITType"]));		//HP_UNITType(핸드폰결제)
	$agspay->SetValue("HP_IDEN",trim($_POST["HP_IDEN"]));				//HP_IDEN(핸드폰결제)
	$agspay->SetValue("HP_IPADDR",trim($_POST["HP_IPADDR"]));			//HP_IPADDR(핸드폰결제)

	/*ARS사용*/
	$agspay->SetValue("ARS_NAME",trim($_POST["ARS_NAME"]));				//ARS_NAME(ARS결제)
	$agspay->SetValue("ARS_PHONE",trim($_POST["ARS_PHONE"]));			//ARS_PHONE(ARS결제)

	/*가상계좌사용*/
	$agspay->SetValue("VIRTUAL_CENTERCD",trim($_POST["VIRTUAL_CENTERCD"]));	//은행코드(가상계좌)
	$agspay->SetValue("VIRTUAL_DEPODT",trim(date('Ymd',strtotime('+5 days')))); //입금예정일(가상계좌)
	$agspay->SetValue("ZuminCode",trim($_POST["ZuminCode"]));				//주민번호(가상계좌)
	$agspay->SetValue("MallPage",trim($_POST["MallPage"]));					//상점 입/출금 통보 페이지(가상계좌)
	$agspay->SetValue("VIRTUAL_NO",trim($_POST["VIRTUAL_NO"]));				//가상계좌번호(가상계좌)

	/*에스크로사용*/
	$agspay->SetValue("ES_SENDNO",trim($_POST["ES_SENDNO"]));				//에스크로전문번호

	/*계좌이체(소켓) 결제 사용 변수*/
	$agspay->SetValue("ICHE_SOCKETYN",trim($_POST["ICHE_SOCKETYN"]));			//계좌이체(소켓) 사용 여부
	$agspay->SetValue("ICHE_POSMTID",trim($_POST["ICHE_POSMTID"]));				//계좌이체(소켓) 이용기관주문번호
	$agspay->SetValue("ICHE_FNBCMTID",trim($_POST["ICHE_FNBCMTID"]));			//계좌이체(소켓) FNBC거래번호
	$agspay->SetValue("ICHE_APTRTS",trim($_POST["ICHE_APTRTS"]));				//계좌이체(소켓) 이체 시각
	$agspay->SetValue("ICHE_REMARK1",trim($_POST["ICHE_REMARK1"]));				//계좌이체(소켓) 기타사항1
	$agspay->SetValue("ICHE_REMARK2",trim($_POST["ICHE_REMARK2"]));				//계좌이체(소켓) 기타사항2
	$agspay->SetValue("ICHE_ECWYN",trim($_POST["ICHE_ECWYN"]));					//계좌이체(소켓) 에스크로여부
	$agspay->SetValue("ICHE_ECWID",trim($_POST["ICHE_ECWID"]));					//계좌이체(소켓) 에스크로ID
	$agspay->SetValue("ICHE_ECWAMT1",trim($_POST["ICHE_ECWAMT1"]));				//계좌이체(소켓) 에스크로결제금액1
	$agspay->SetValue("ICHE_ECWAMT2",trim($_POST["ICHE_ECWAMT2"]));				//계좌이체(소켓) 에스크로결제금액2
	$agspay->SetValue("ICHE_CASHYN",trim($_POST["ICHE_CASHYN"]));				//계좌이체(소켓) 현금영수증발행여부
	$agspay->SetValue("ICHE_CASHGUBUN_CD",trim($_POST["ICHE_CASHGUBUN_CD"]));	//계좌이체(소켓) 현금영수증구분
	$agspay->SetValue("ICHE_CASHID_NO",trim($_POST["ICHE_CASHID_NO"]));			//계좌이체(소켓) 현금영수증신분확인번호

	/*계좌이체-텔래뱅킹(소켓) 결제 사용 변수*/
	$agspay->SetValue("ICHEARS_SOCKETYN", trim($_POST["ICHEARS_SOCKETYN"]));	//텔레뱅킹계좌이체(소켓) 사용 여부
	$agspay->SetValue("ICHEARS_ADMNO", trim($_POST["ICHEARS_ADMNO"]));			//텔레뱅킹계좌이체 승인번호
	$agspay->SetValue("ICHEARS_POSMTID", trim($_POST["ICHEARS_POSMTID"]));		//텔레뱅킹계좌이체 이용기관주문번호
	$agspay->SetValue("ICHEARS_CENTERCD", trim($_POST["ICHEARS_CENTERCD"]));	//텔레뱅킹계좌이체 은행코드
	$agspay->SetValue("ICHEARS_HPNO", trim($_POST["ICHEARS_HPNO"]));			//텔레뱅킹계좌이체 휴대폰번호

	if($compareResult == "S010" || $compareResult == "S050" || $compareResult == "S011") {
		$agspay->startPay();


		/* +----------------------------------------------------------------------------------------------+
		' |  결과 처리
		' +----------------------------------------------------------------------------------------------+*/
		$ono		= $agspay->GetResult("rOrdNo"); // 주문번호
		$rDealNo	= $agspay->GetResult("rDealNo"); // 거래번호
		$rApprNo	= $agspay->GetResult("rApprNo"); // 승인번호
		$rAppTm		= $agspay->GetResult("rApprTm"); // 승인시각
		$rCardCd	= $agspay->GetResult("rCardCd"); // 카드사코드
		$card_name	= $_card_name[$rCardCd];
		$quota		= $agspay->REQUEST['partial_mm']; // 할부정보
		$res_msg	= addslashes(mb_convert_encoding($agspay->GetResult("rResMsg"), 'utf8', 'euckr'));
		$pay_method	= $agspay->REQUEST['AuthTy']; // 결제방식
		$SubTy		= $agspay->REQUEST['SubTy']; // 결제방식

		$ES_SENDNO	= $agspay->GetResult("ES_SENDNO"); // 에스크로 주문번호
		$rAuthTy	= $agspay->GetResult("rAuthTy"); // (가상계좌 일반 : vir_n 유클릭 : vir_u 에스크로 : vir_s)
		$rVirNo		= $agspay->GetResult("rVirNo"); // 가상계좌번호
		$bank_code	= $agspay->GetResult("VIRTUAL_CENTERCD"); // 가상계좌 은행종류

		$rHP_DATE	= $agspay->GetResult("rHP_DATE"); // 핸드폰결제일
		$rHP_TID	= $agspay->GetResult("rHP_TID"); // 핸드폰결제 TID

		foreach($agspay->REQUEST as $key => $val) {
			${'req_'.$key} = addslashes(iconv($val, 'euc-kr', _BASE_CHARSET_));
		}

		if($agspay->GetResult("rSuccYn") == "y") {
			$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
			$card_tbl = ($pay_method == 'virtual') ? $tbl['vbank'] : $tbl['card'];
			$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'"); // 카드, 가상계좌 결제 정보

			if(!$card['no'] || $card['stat'] != '1') msg("잘못된 주문 정보입니다");

			if($pay_method == 'virtual') {
				$asql .= ", `bank_code`='$bank_code', `account`='$rVirNo', `bankname`='', `ipgm_time`='$rAppTm', `op_cd`='$rApprNo'";
				$rDealNo = $ES_SENDNO;
			}
			else $asql = ", `card_cd`='$rCardCd' ,`card_name`='$card_name', `app_time`='$rAppTm' ,`app_no`='$rApprNo',`quota`='$quota'";

			$pdo->query("
				update `$card_tbl` set`stat`='2'
					,`env_info`='$SubTy', `res_msg`='$res_msg'
					,`good_mny`='$req_Amt', `good_name`='$req_ProdNm', `buyr_name`='$req_OrdNm', `buyr_mail`='$req_UserEmail', `buyr_tel1`='$req_OrdPhone'
					,`ordr_idxx`='$ono' ,`tno`='$rDealNo',`use_pay_method`='$pay_method' $asql
				where `wm_ono`='$ono'
			");

			makePGLog($ono, 'allthegate success');

			include_once $engine_dir.'/_engine/order/order2.exe.php';

		} else {
			$pdo->query("update `$card_tbl` set stat=3, res_msg='$res_msg' where wm_ono='$ono'");
			$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$ono' and stat != 2");
			$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$ono' and stat != 2");

			makePGLog($ono, 'allthegate faild');

			msg("결제가 실패하였습니다. 다시 결제 해 주십시오.\\n".php2java($res_msg));
		}
		exit;
	} else if($compareResult == "E911" || $compareResult == "E951") {
		$errmsg = '결제금액이 상점에서 설정한 최소 금액보다 작습니다.';
	} else if($compareResult == "E919" || $compareResult == "E959") {
		$errmsg = '결제금액이 위변조 검증한 값과 다릅니다.';
	} else if($compareResult == "E952") {
		$errmsg = 'DB에 저장된 값이 없습니다.';
	} else if($compareResult == "E912") {
		$errmsg = '세션이 만료 되었습니다.';
	} else if($compareResult == "E981" || $compareResult == "E982") {
		$errmsg = '위변조 설정이 잘못되었습니다.';
	} else if($compareResult == "E979") {
		$errmsg = 'DB 관련 에러가 발생하였습니다.';
	} else if($compareResult == "E984") {
		$errmsg = '금액에 숫자가 아닌 다른값이 있습니다.';
	} else if($compareResult == "E983") {
		$errmsg = '설정값과 파라미터값이 일치하지 않습니다.';
	}

	if($errmsg) {
		$pdo->query("update `$card_tbl` set stat=3, res_msg='".addslashes($errmsg)."' where wm_ono='$ono'");
		$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$ono' and stat != 2");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$ono' and stat != 2");

		makePGLog($ono, 'allthegate faild');

		msg("결제가 실패하였습니다. 다시 결제 해 주십시오.\\n".$errmsg);
	}

	exit;

?>