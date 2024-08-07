<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KSPAY 결제 부분취소
	' +----------------------------------------------------------------------------------------------+*/

	$card = $pdo->assoc("select * from `$tbl[card]` where `no` = '$cno'");
	if(!$card['wm_ono']) msg('존재하지 않는 카드결제 코드입니다.');

	$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$card[wm_ono]'");

	$storeid = ($ord['mobile'] == 'Y') ? $cfg['kspay_m_storeid'] : $storeid = $cfg['kspay_storeid'];
	$storepasswd = '';
	$cancel_type = ($price == 0) ? '0' : '3';
	$canc_seq = $pdo->row("select count(*) from $tbl[card_cc_log] where stat=2 and ono='$ord[ono]'")+1;


	include $engine_dir.'/_engine/card.kspay/KSPayApprovalCancel.inc.php';

	$EncType = '2';
	$Version = '0210';		// 전문버전
	$VersionType = '00';	// 구분
	$Resend = '0';			// 전송구분 : 0 : 처음,  2: 재전송

	$RequestDate = SetZero(strftime('%Y'),4).SetZero(strftime('%m'),2).SetZero(strftime('%d'),2).SetZero(strftime('%H'),2).SetZero(strftime('%M'),2).SetZero(strftime('%S'),2);
	$KeyInType     = 'K';   // KeyInType 여부 : S : Swap, K: KeyInType
	$LineType      = '1';   // lineType 0 : offline, 1:internet, 2:Mobile
	$ApprovalCount = '1';   // 복합승인갯수
	$GoodType      = '0';   // 제품구분 0 : 실물, 1 : 디지털
	$HeadFiller    = '';	// 예비

// Header (입력값 (*) 필수항목)--------------------------------------------------
	$StoreId		= $storeid;
	$OrderNumber	= '';	// *주문번호
	$UserName		= '';	// *주문자명
	$IdNum		    = '';	// 주민번호 or 사업자번호
	$Email			= '';	// *email
	$GoodName		= '';	// *제품명
	$PhoneNo		= '';	// *휴대폰번호
// Header end -------------------------------------------------------------------

// Data Default(수정항목이 아님)-------------------------------------------------
	$ApprovalType   = 1010;	// 승인구분
	$TransactionNo  = $card['tno'];		// 거래번호
	$Canc_amt       = $price;	//' 취소금액
	$Canc_seq       = $seq;	//' 취소일련번호
	$Canc_type      = $cancel_type;	//' 취소유형 0 :거래번호취소 1: 주문번호취소 3:부분취소
// Data Default end -------------------------------------------------------------

// Server로 부터 응답이 없을시 자체응답
	$rApprovalType     = "1011";
	$rTransactionNo    = "";              // 거래번호
	$rStatus           = "X";             // 상태 O : 승인, X : 거절
	$rTradeDate        = "";              // 거래일자
	$rTradeTime        = "";              // 거래시간
	$rIssCode          = "00";            // 발급사코드
	$rAquCode          = "00";            // 매입사코드
	$rAuthNo           = "9999";          // 승인번호 or 거절시 오류코드
	$rMessage1         = "취소실패";      // 메시지1
	$rMessage2         = "";			  // 메시지2
	$rCardNo           = "";              // 카드번호
	$rExpDate          = "";              // 유효기간
	$rInstallment      = "";              // 할부
	$rAmount           = "";              // 금액
	$rMerchantNo       = "";              // 가맹점번호
	$rAuthSendType     = "N";             // 전송구분
	$rApprovalSendType = "N";             // 전송구분(0 : 거절, 1 : 승인, 2: 원카드)
	$rPoint1           = "000000000000";  // Point1
	$rPoint2           = "000000000000";  // Point2
	$rPoint3           = "000000000000";  // Point3
	$rPoint4           = "000000000000";  // Point4
	$rVanTransactionNo = "";
	$rFiller           = "";              // 예비
	$rAuthType         = "";              // ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
	$rMPIPositionType  = "";              // K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
	$rMPIReUseType     = "";              // Y : 재사용, N : 재사용아님
	$rEncData          = "";              // MPI, ISP 데이터
// --------------------------------------------------------------------------------

	KSPayApprovalCancel("localhost", 29991);

	HeadMessage(
		$EncType       ,                  // 0: 암화안함, 1:openssl, 2: seed
		$Version       ,                  // 전문버전
		$VersionType   ,                  // 구분
		$Resend        ,                  // 전송구분 : 0 : 처음,  2: 재전송
		$RequestDate   ,                  // 재사용구분
		$StoreId       ,                  // 상점아이디
		$OrderNumber   ,                  // 주문번호
		$UserName      ,                  // 주문자명
		$IdNum         ,                  // 주민번호 or 사업자번호
		$Email         ,                  // email
		$GoodType      ,                  // 제품구분 0 : 실물, 1 : 디지털
		$GoodName      ,                  // 제품명
		$KeyInType     ,                  // KeyInType 여부 : S : Swap, K: KeyInType
		$LineType      ,                  // lineType 0 : offline, 1:internet, 2:Mobile
		$PhoneNo       ,                  // 휴대폰번호
		$ApprovalCount ,                  // 복합승인갯수
		$HeadFiller    );                 // 예비

// ------------------------------------------------------------------------------

	if($Canc_type == '3'){
		CancelDataMessage($ApprovalType, $Canc_type, $TransactionNo,	"",	"", SetZero($Canc_amt,9).SetZero($Canc_seq,2),	"", "");
	}
	else{
		CancelDataMessage($ApprovalType, "0", $TransactionNo,	"",	"", "",	"", "");
	}

	if(SendSocket("1")) {
		$rApprovalType		= $ApprovalType	    ;
		$rTransactionNo		= $TransactionNo	;  	// 거래번호
		$rStatus			= $Status		  	;	// 상태 O : 승인, X : 거절
		$rTradeDate			= $TradeDate		;  	// 거래일자
		$rTradeTime			= $TradeTime		;  	// 거래시간
		$rIssCode			= $IssCode		  	;	// 발급사코드
		$rAquCode			= $AquCode		  	;	// 매입사코드
		$rAuthNo			= $AuthNo		  	;	// 승인번호 or 거절시 오류코드
		$rMessage1			= $Message1		  	;	// 메시지1
		$rMessage2			= $Message2		  	;	// 메시지2
		$rCardNo			= $CardNo		  	;	// 카드번호
		$rExpDate			= $ExpDate		  	;	// 유효기간
		$rInstallment		= $Installment	  	;	// 할부
		$rAmount			= $Amount		  	;	// 금액
		$rMerchantNo		= $MerchantNo	  	;	// 가맹점번호
		$rAuthSendType		= $AuthSendType	  	;	// 전송구분= new String(this.read(2))
		$rApprovalSendType	= $ApprovalSendType	;	// 전송구분(0 : 거절, 1 : 승인, 2: 원카드)
		$rPoint1			= $Point1		  	;	// Point1
		$rPoint2			= $Point2		  	;	// Point2
		$rPoint3			= $Point3		  	;	// Point3
		$rPoint4			= $Point4		  	;	// Point4
		$rVanTransactionNo  = $VanTransactionNo ;   // Van거래번호
		$rFiller			= $Filler		  	;	// 예비
		$rAuthType			= $AuthType		  	;	// ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
		$rMPIPositionType	= $MPIPositionType 	;	// K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
		$rMPIReUseType		= $MPIReUseType		;	// Y : 재사용, N : 재사용아님
		$rEncData			= $EncData		  	;	// MPI, ISP 데이터
	}


	// db 처리
	$respmsg = trim($rMessage1.' '.$rMessage2);
	if($rStatus == 'O') {
		$stat = 2;
		$msg = '거래취소성공!';
		$cstat = ($card['wm_price'] == $price) ? 3 : 2;
		$pdo->query("update `$tbl[card]` set `stat`='$cstat', `wm_price` = `wm_price`-'$price' where `no`='$card[no]'");
	} else {
		$stat = 1;
		$msg = '거래취소실패! ('.$respcode.' : '.addslashes($respmsg).')';
	}

	$respmsg = addslashes($respmsg);
	$pdo->query("
		insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$stat', '$card[wm_ono]', '$price', '$card[tno]', '$rAuthNo', '$respmsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

	msg($respmsg, 'reload', 'parent');

?>