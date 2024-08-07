<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIlite 결제결과 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	// 라이브러리 인클루드
	require $engine_dir."/_engine/card.inicis/INILite/libs/INILiteLib.php";

	// INILite 클래스의 인스턴스 생성 *
	$inipay=new INILite;

	// 배송등록/구매결정 정보 설정 *
	$inipay->m_inipayHome		= $engine_dir."/_engine/card.inicis/INILite";		// 상점 수정 필요
	$inipay->m_inipayLogHome	= $root_dir."/_data/INILite";						// 상점 수정 필요
	$inipay->m_key				= $cfg['card_inicis_key'];							// 상점 수정 필요
	$inipay->m_ssl				= "false";											// ssl지원하면 true로 셋팅해 주세요.
	$inipay->m_type				= "escrow";											// 고정
	$inipay->m_escrowtype		= $escrow_type;										// dlv : 배송등록, confirm : 구매결정
	$inipay->m_log				= "true";											// true로 설정하면 로그가 생성됨(적극권장)
	$inipay->m_debug			= "true";											// 로그모드("true"로 설정하면 상세로그가 생성됨. 적극권장)

	if($escrow_type == 'confirm') {
		$inipay->m_mid				= $mid;												// 상점아이디
		$inipay->m_tid				= $tid;												// 원거래아이디
		$inipay->m_encrypted		= $encrypted;										// 암호문
		$inipay->m_sessionKey		= $sessionkey;										// 암호문
	} else {
		$inipay->m_mid				= $mid;												// 상점아이디
		$inipay->m_tid				= $tid;												// 원거래아이디
		$inipay->m_dlv_ip			= getenv("REMOTE_ADDR");							// 아이피
		$inipay->m_oid				= $oid;												// 주문번호
		$inipay->m_dlv_date			= $dlv_date;										// 배송일
		$inipay->m_dlv_time			= $dlv_time;										// 배송시간
		$inipay->m_dlv_report		= $dlv_report;										// I : 배송등록, U : 배송수정
		$inipay->m_dlv_invoice		= $invoice;											// 운송장번호
		$inipay->m_dlv_name			= $dlv_name;										// 배송등록자
		$inipay->m_dlv_excode		= $dlv_exCode;										// 택배사코드
		$inipay->m_dlv_exname		= $dlv_exName;										// 택배사명
		$inipay->m_dlv_charge		= $dlv_charge;										// 배송비 지급형태, SH : 판매자부담, BH : 구매자부담
		$inipay->m_dlv_invoiceday	= date('Y-m-d H:i:s');								// 배송등록 확인일자
		$inipay->m_dlv_sendname		= $sendName;										// 송신인명
		$inipay->m_dlv_sendpost		= $sendPost;										// 송신인우편번호
		$inipay->m_dlv_sendaddr1	= $sendAddr1;										// 송신인주소
		$inipay->m_dlv_sendaddr2	= $sendAddr2;										// 송신인상세주소
		$inipay->m_dlv_sendtel		= $sendTel;											// 송신인전화번호
		$inipay->m_dlv_recvname		= $recvName;										// 수취인명
		$inipay->m_dlv_recvpost		= $recvPost;										// 수취인우편번호
		$inipay->m_dlv_recvaddr		= $recvAddr;										// 수취인주소
		$inipay->m_dlv_recvtel		= $recvTel;											// 수취인전화번호
		$inipay->m_dlv_goodscode	= $goodsCode;										// 상품코드
		$inipay->m_dlv_goods		= $goods;											// 상품명
		$inipay->m_dlv_goodcnt		= $goodCnt;											// 상품수량
		$inipay->m_price			= $price;											// 상품가격
	}

	$inipay->startAction();

	$resultCode					= $inipay->m_resultCode;							// 결과코드 ("00"이면 성공)
	$resultMsg					= $inipay->m_resultMsg; 							// 결과내용 (결과에 대한 설명)
	$pgAuthDate					= $inipay->m_pgAuthDate;							// 처리날짜
	$pgAuthTime					= $inipay->m_pgAuthTime;							// 처리시각
	$escrowtype					= $inipay->m_escrowtype;							// confirm_cnf : 구매확인, confirm_dny : 구매거절

	echo $resultCode.'::'.$resultMsg;

	if($escrow_type == 'confirm') {
		if($resultCode == '00') {
			?>
			<script type="text/javascript">
				gurl="<?=$root_url?>/main/exec.php?exec_file=mypage/receive.exe.php&ono=<?=$oid?>&confirm=<?=$inipay->m_escrowtype?>";
				document.location.href=gurl;
			</script>
			<?
		} else {
			alert(php2java("[".$resultCode."] ".$resultMsg));
		}
	}
?>