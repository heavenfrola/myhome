<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 에스크로 입금 결과 전송
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	require($engine_dir."/_engine/card.inicis/EscrowLib.php");

	// 인스턴스 설정
	$escrow = new Escrow;


	// 배송/반품 공통 정보 설정
	$escrow->inipayhome = file_exists($root_dir.'/_data/INIpay41') ? $root_dir.'/_data/INIpay41' : $root_dir.'/INIpay41'; // 이니페이 홈디렉터리 ( 2009-11-25 by zardsama)

	$escrow->mid = $mid;					// 상점 아이디
	$escrow->EscrowType=$EscrowType;          		// 에스크로 타입
	$escrow->hanatid = $hanatid;	         		// 하나은행 거래 아이디
	$escrow->invno = $invno;				// 운송장 번호
	$escrow->adminID = $adminID;				// 등록자 ID
	$escrow->adminName = $adminName;			// 등록자 성명
	$escrow->regdate = date("Ymd");				// 등록요청 일자
	$escrow->regtime = date("His");				// 등록요청 시간


	// 배송관련 정보 설정
	$escrow->compName = $compName;				// 배송회사명
	$escrow->compID = $compID;				// 배송회사
	$escrow->transtype = $transtype;		        // 배송종류, 배송지시 일경우 - 운송 구분 (0 - 배송, 1 - 반송)
	$escrow->transport = $transport;			// 운송수단
	$escrow->transfee = $transfee;				// 배송비
	$escrow->paymeth = $paymeth;				// 배송비 지급방법
	$escrow->notice = $notice;				// 배송 주의 사항
	$escrow->transdate1 = $transdate1;		        // 배송요청일자 (from)
	$escrow->transdate2 = $transdate2;                      // 배송요청일자 (to)
	$escrow->cnt = "1";		                        // 배송요청 메세지 겟수(배송정보 등록시에만 필요함)

	// 배송지시 정보 설정
	$escrow->transid = $transid;				// 택배사 ID
	$escrow->customcode = $customcode;			// 고객사 코드
	$escrow->orderno = $orderno;				// 주문번호(출하/지시)
	$escrow->settleno = $settleno;				// 결제 번호
	$escrow->pgid = "PINICIS001";				// PGID (고정-절대 수정 불가)
	$escrow->sendareacode = $sendareacode;			// 출고지 코드
	$escrow->mername = $mername;				// 제휴사명 (상점 이름)
	$escrow->sendtel = $sendtel;				// 집하지 전화번호
	$escrow->sendzip = $sendzip;				// 집하지 우편번호
	$escrow->sendaddr = $sendaddr;				// 집하지 주소
	$escrow->sendhpp = $sendhpp;				// 집하지담당자핸드폰 번호
	$escrow->sendaddr1 = $sendaddr1;			// 집하지 상세 주소
	$escrow->recvname = $recvname;				// 수취인 이름 (또는 구매자 명)
	$escrow->recvtel = $recvtel;				// 수취인 전화번호
	$escrow->recvzip = $recvzip;				// 수취인 우편번호
	$escrow->recvaddr = $recvaddr;				// 수취인 주소
	$escrow->recvaddr1 = $recvaddr1;			// 수취인 상세주소
	$escrow->recvhpp = $recvhpp;				// 수취인 핸드폰 번호
	$escrow->ordertype = $ordertype;			// 지시 구분 (1 - 일반, 2 - 교환, 3 - A S)
	$escrow->feetype = $feetype;				// 운임 구분 (1 - 선불, 2 - 착불, 3 - 신용, 4 - 착지신용)
	$escrow->boxtype = $boxtype;				// 박스 타입 (1 - 소, 2 - 중, 3 - 대)
	$escrow->goodcode = $goodcode;				// 대표 상품코드
	$escrow->goodname = $goodname;				// 대표 상품명
	$escrow->qty = $qty;					// 수량
	$escrow->origininvoice = $origininvoice;			// 원 배송 송장번호(반송시에 사용)
	$escrow->goodsort = $goodsort;				// 총상품 종류 수
	$escrow->transmsg = $transmsg;				// 전달 사항
	$escrow->orderremark = $orderremark;			// 주문 메모
	$escrow->orderdate = date("Ymdhms");			// 주문날짜시간

	// 반품관련 정보 설정
	$escrow->returntype = $returntype;                      // 반품 유형 (반품승인 : 0, 반품거절 : 1)
	$escrow->returncode = $returncode;                      // 반품승인여부(기배송완료 : R0, 기서비스 제공 : R1, 반품물품 미수령 : R2, 반품물품이 원상품과 다름 : R3, 부당구매 철회 : R4, 기타 : R5)
	$escrow->reMsg = $reMsg;                                // 반품거부사유메세지

	// 배송정보/반품정보 메세지 생성
	if($escrow->EscrowType == "dr") { // 배송정보 등록
		$escrow->sendMsg = $escrow->mid."\x0B".
							$escrow->cnt."\x0B".
							$escrow->hanatid.'|'.
							$escrow->invno.'|'.
							$escrow->adminID.'|'.
							$escrow->adminName.'|'.
							$escrow->regdate.'|'.
							$escrow->regtime.'|'.
							$escrow->compName.'|'.
							$escrow->compID.'|'.
							$escrow->transtype.'|'.
							$escrow->transport.'|'.
							$escrow->transfee.'|'.
							$escrow->paymeth.'|'.
							$escrow->notice.'|'.
							$escrow->transdate1.'|'.
							$escrow->transdate2;
	} else if($escrow->EscrowType == 'du') { // 배송정보 변경
		$escrow->sendMsg = $escrow->mid."\x0B".
							$escrow->hanatid."\x0B".
							$escrow->invno."\x0B".
							$escrow->adminID."\x0B".
							$escrow->adminName."\x0B".
							$escrow->regdate."\x0B".
							$escrow->regtime."\x0B".
							$escrow->compName."\x0B".
							$escrow->compID."\x0B".
							$escrow->transtype."\x0B".
							$escrow->transport."\x0B".
							$escrow->transfee."\x0B".
							$escrow->paymeth."\x0B".
							$escrow->notice."\x0B".
							$escrow->transdate1."\x0B".
							$escrow->transdate2;
	} else if($escrow->EscrowType == 'dd') { // 배송지시
		$escrow->sendMsg = $escrow->mid."\x0B".
							$escrow->hanatid."\x0B".
							$escrow->transtype."\x0B".
							$escrow->transid."\x0B".
							$escrow->customcode."\x0B".
							$escrow->orderno."\x0B".
							$escrow->settleno."\x0B".
							$escrow->pgid."\x0B".
							$escrow->sendareacode."\x0B".
							$escrow->mername."\x0B".
							$escrow->sendtel."\x0B".
							$escrow->sendzip."\x0B".
							$escrow->sendaddr."\x0B".
							$escrow->sendhpp."\x0B".
							$escrow->sendaddr1."\x0B".
							$escrow->recvname."\x0B".
							$escrow->recvtel."\x0B".
							$escrow->recvzip."\x0B".
							$escrow->recvaddr."\x0B".
							$escrow->recvaddr1."\x0B".
							$escrow->recvhpp."\x0B".
							$escrow->ordertype."\x0B".
							$escrow->feetype."\x0B".
							$escrow->boxtype."\x0B".
							$escrow->goodcode."\x0B".
							$escrow->goodname."\x0B".
							$escrow->qty."\x0B".
							$escrow->origininvoice."\x0B".
							$escrow->goodsort."\x0B".
							$escrow->transmsg."\x0B".
							$escrow->orderremark."\x0B".
							$escrow->orderdate;

	} else if($escrow->EscrowType == 'rr') { // 반품정보 등록
		$escrow->sendMsg = $escrow->mid."\x0B".
							$escrow->hanatid."\x0B".
							$escrow->adminID."\x0B".
							$escrow->adminName."\x0B".
							$escrow->regdate."\x0B".
							$escrow->regtime."\x0B".
							$escrow->returntype."\x0B".
							$escrow->returncode."\x0B".
							$escrow->reMsg;
	} else if($escrow->EscrowType == 'ru') { // 반품정보 변경
		$escrow->sendMsg = $escrow->mid."\x0B".
							$escrow->hanatid."\x0B".
							$escrow->adminID."\x0B".
							$escrow->adminName."\x0B".
							$escrow->regdate."\x0B".
							$escrow->regtime."\x0B".
							$escrow->returntype."\x0B".
							$escrow->returncode."\x0B".
							$escrow->reMsg;
	}


	// 배송정보/반품정보 요청
	$escrow->startAction();


	// 배송정보/반품정보 결과
	if($execSilent) {
		exit($escrow->resultMsg);
	}
	msg("","reload","parent");
?>