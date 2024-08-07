<?php

namespace Wing\API\Naver;

/*
 *  네이버 페이 API
 */
Class CheckoutApi4 {

	private $shop_idx = 0;
	private $result = '';

	public function __construct() {
		global $wec;

		$this->shop_idx = $wec->config['account_idx'];
		$this->key_code = $wec->config['wm_key_code'];
	}

	public function api($operation = '') {
		global $cfg, $tbl, $pdo, $MoreDataTimeFrom;

		$this->error = '';

		if(!$operation) $this->error('checkout no operation!');

		$param = '?shop_idx='.$this->shop_idx.'&key_code='.$this->key_code.'&operation='.$operation;
		if($operation != 'GetCustomerInquiryList' && $operation != 'AnswerCustomerInquiry') {
			$param .= '&npay_ver='.$cfg['npay_ver'];
		}

		$args = func_get_args();
		foreach($args as $key => $val) {
			if($key == 0) continue;
			$param .= "&args[]=".urlencode($val);
		}

        $start_date = date('Y-m-d H:i:s');
		$result = comm('http://checkout.wisa.ne.kr/', $param);
        if (!trim($result)) { // 정상 응답 없을 경우
            $error = '네이버페이 통신 에러';
        }

		$this->result = $result;
        $end_date = date('Y-m-d H:i:s');
        $admin_id = $GLOBALS['admin']['admin_id'];
        if (!$admin_id) $admin_id = '';

		$data = simplexml_load_string($result, null, LIBXML_NOERROR);
        if(is_object($data) == true) {
            $error = simplexmlToString($data->Error);
            $error = trim(str_replace('error : ', '', $error));
        } else { // 정상 응답 없을 경우
            $error = '네이버페이 데이터 형식 에러';
        }

        if (empty($error) == false && in_array($operation, array('GetPurchaseReviewList', 'GetProductOrderInfoList', 'GetChangedProductOrderList', 'GetCustomerInquiryList', 'ShipProductOrder', 'PlaceProductOrder')) == false) {
            $this->createLogTable();
            $pdo->query("
                insert into {$tbl['npay_api_log']} (operation, args, args1, post_vars, get_vars, result, errors, admin_id, start_date, end_date)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", array(
                $operation, json_encode($args), $args[1], json_encode($_POST), json_encode($_GET), $result, $error, $admin_id, $start_date, $end_date
            ));
        }

		$MoreDataTimeFrom = simplexmlToString($data->MoreDataTimeFrom);

		if($error) {
			$this->error($error);
			return false;
		}

		if($data->ReturnedDataCount[0] > 0) {
			$_tmp = $data->datas[0]->Data;
			$result = array();
			foreach($_tmp as $val) {
				$result[] = $val;
			}
			return $result;
		}
	}

	public function getStat($productOrder) {
		$status = $productOrder->ProductOrderStatus[0];
		$claim_type = $productOrder->ClaimType[0];
		$Claim_status = $productOrder ->ClaimStatus[0];

		switch($status) {
			case 'PAYMENT_WAITING' : $stat = 1; break;
			case 'PAYED' : $stat = 2; break;
			case 'DELIVERING' : $stat = 4; break;
			case 'DELIVERED' : $stat = 5; break;
			case 'PURCHASE_DECIDED' : $stat = 5; break;
			case 'EXCHANGE' : $stat = 19; break;
			case 'CHANGED' : $stat = 13; break;
			case 'RETURNED' : $stat = 17; break;
			case 'CANCELED_BY_NOPAYMENT' : $stat = 13; break;
		}

		if($stat == 2 && $productOrder->PlaceOrderStatus[0] == 'OK') { // 상품준비중
			$stat = 3;
		}

		if($claim_type == 'CANCEL') {
			switch($Claim_status) {
				case 'CANCEL_REQUEST' : $stat = 12; break;	// 취소요청
				case 'CANCELING' : $stat = 12; break;	// 취소 처리중
				case 'CANCEL_DONE' : $stat = 13; break;	// 취소처리완료
				case 'CANCELED' : $stat = 13; break;	// 취소완료
				case 'ADMIN_CANCEL_DONE' : $stat = 13; break;	// 취소완료
				case 'CANCEL_REJECT' : break;	// 취소철회
			}
		}
		else if($claim_type == 'RETURN') {
			switch($Claim_status) {
				case 'RETURN_REQUEST' : $stat = 16; break;	// 반품요청
				case 'COLLECTING' : $stat = 22; break;	// 수거처리중
				case 'COLLECT_DONE' : $stat = 23; break;	// 수거완료
				case 'RETURN_DONE' : $stat = 17; break;	// 반품완료
				case 'RETURN_REJECT' : break;	// 반품철회
			}
		}
		else if($claim_type == 'EXCHANGE') {
			switch($Claim_status) {
				case 'EXCHANGE_REQUEST' : $stat = 18; break;	// 교환요청
				case 'COLLECTING' : $stat = 24; break;	// 수거처리중
				case 'COLLECT_DONE' : $stat = 25; break;	// 수거완료
				case 'EXCHANGE_REDELIVERING' : $stat = 4; break;	// 교환재배송중
				case 'EXCHANGE_DONE' : $stat = 5; break;	// 교환완료
				case 'EXCHANGE_REJECT' : break;	// 교환철회
			}
		}
		else if($claim_type == 'ADMIN_CANCEL') {
			if(!$productOrder->PaymentDate[0]) {
				$stat = 13;
			} else if(!$productOrder->DeliveredDate[0]) {
				$stat = 13;
			} else {
				$stat = 17;
			}
		}

		return $stat;
	}

	public function getPayType($str) {
		$str = str_replace(' 간편결제', '', $str);
		switch($str) {
			case '신용카드' : $pay_type = 1; break;
			case '무통장입금' : $pay_type = 2; break;
			case '실시간계좌이체' : $pay_type = 5; break;
			case '계좌' : $pay_type = 5; break;
			case '휴대폰결제' : $pay_type = 7; break;
			case '휴대폰' : $pay_type = 7; break;
			case '포인트결제' : $pay_type = 3; break;
			case '네이버 캐쉬' : $pay_type = 'C'; break;
			case '나중에결제' : $pay_type = 24; break;
			case '후불결제' : $pay_type = 26; break;
		}
		return $pay_type;
	}

	public function getDlvType($str) {
		/*
		DELIVERY 일반 택배
		GDFW_ISSUE_SVC 굿스플로 송장 출력
		VISIT_RECEIPT 방문 수령
		DIRECT_DELIVERY 직접 전달
		QUICK_SVC 퀵서비스
		NOTHING 배송 없음
		RETURN_DESIGNATED 지정 반품 택배
		RETURN_DELIVERY 일반 반품 택배
		RETURN_INDIVIDUAL 직접 반송
		*/
	}

	public function getDlvPrv($str) {
		global $tbl, $pdo;

		if(!$str) return '';

		switch($str) {
			case 'CJGLS' :
				$dlv_name = array('CJGLS', 'CJ대한통운');
			break;
			case 'SAGAWA': $dlv_name = 'SC로지스'; break;
			case 'YELLOW' : $dlv_name = '옐로우캡 택배'; break;
			case 'KGB' : $dlv_name = '로젠'; break;
			case 'DONGBU' :
				$dlv_name = array('동부익스프레스', 'KG로지스', '드림택배');
			break;
			case 'EPOST' : $dlv_name = '우체국택배'; break;
			case 'HANJIN' : $dlv_name = '한진택배'; break;
			case 'HYUNDAI' : $dlv_name = '롯데택배'; break;
			case 'KGBLS' : $dlv_name = 'KGB 택배'; break;
			case 'HANARO' : $dlv_name = '하나로 로지스'; break;
			case 'DONGBU' : $dlv_name = 'KG로지스'; break;
			case 'KDEXP' : $dlv_name = '경동택배'; break;
			case 'CHUNIL' : $dlv_name = '천일택배'; break;
			case 'CVSNET' : $dlv_name = '편의점택배'; break;
            case 'HLCGLOBAL' : $dlv_name = '롯데글로벌로지스'; break;
			case 'KUNYOUNG' : $dlv_name = '건영택배'; break;
			case 'CUPARCEL' : $dlv_name = 'CU 편의점택배'; break;
		}

		if(is_array($dlv_name)) {
			$dlv_names = '';
			foreach($dlv_name as $key => $val) {
				$dlv_names .= ",'$val'";
			}
			$dlv_names = substr($dlv_names, 1);
			$dlv_name = $pdo->row("select name from $tbl[delivery_url] where replace(name, ' ', '') in ($dlv_names) order by no desc limit 1");
		}

		return $dlv_name;
	}

	public function getDlvCode($str) {
		switch(str_replace(' ', '', strtoupper($str))) {
			case 'CJ대한통운' : $code = 'CJGLS'; break;
			case '대한통운' : $code = 'CJGLS'; break;
			case 'CJGLS' : $code = 'CJGLS'; break;
			case 'SC로지스': $code = 'SAGAWA'; break;
			case '옐로우캡 택배' : $code = 'YELLOW'; break;
			case '로젠' : $code = 'KGB'; break;
			case '동부익스프레스' : $code = 'DONGBU'; break;
			case 'KG로지스' : $code = 'DONGBU'; break;
			case '드림택배' : $code = 'DONGBU'; break;
			case '우체국택배' : $code = 'EPOST'; break;
			case '한진택배' : $code = 'HANJIN'; break;
			case '현대택배' : $code = 'HYUNDAI'; break;
			case '롯데택배' : $code = 'HYUNDAI'; break;
			case 'KGB택배' : $code = 'KGBLS'; break;
			case '하나로로지스' : $code = 'HANARO'; break;
			case 'KG로지스' : $code = 'DONGBU'; break;
			case '경동택배' : $code = 'KDEXP'; break;
			case '천일택배' : $code = 'CHUNIL'; break;
			case '대신택배' : $code = 'DAESIN'; break;
			case '편의점택배' : $code = 'CVSNET'; break;
			case '롯데글로벌로지스' : $code = 'HLCGLOBAL'; break;
			case '건영택배' : $code = 'KUNYOUNG'; break;
			case 'CU 편의점택배' : $code = 'CUPARCEL'; break;
			default : $code = 'CH1'; break;
		}

		return $code;
	}

	public function getClaimReason($str) {
		switch($str) {
			case 'PRODUCT_UNSATISFIED' : $code = '서비스 및 상품 불만족'; break;
			case 'DELAYED_DELIVERY' : $code = '배송 지연'; break;
			case 'SOLD_OUT' : $code = '상품 품절'; break;
			case 'INTENT_CHANGED' : $code = '구매 의사 취소'; break;
			case 'COLOR_AND_SIZE' : $code = '색상 및 사이즈 변경'; break;
			case 'WRONG_ORDER' : $code = '다른 상품 잘못 주문'; break;
			case 'PRODUCT_UNSATISFIED' : $code = '서비스 및 상품 불만족'; break;
			case 'DELAYED_DELIVERY' : $code = '배송 지연'; break;
			case 'SOLD_OUT' : $code = '상품 품절'; break;
			case 'DROPPED_DELIVERY' : $code = '배송 누락'; break;
			case 'NOT_YET_DELIVERY' : $code = '미배송'; break;
			case 'BROKEN' : $code = '상품 파손'; break;
			case 'INCORRECT_INFO' : $code = '상품 정보 상이'; break;
			case 'WRONG_DELIVERY' : $code = '오배송'; break;
			case 'WRONG_OPTION' : $code = '색상 등이 다른 상품을 잘못 배송'; break;
			case 'ETC' : $code ='기타'; break;
		}
		return $code;
	}

	public function getWithHoldReturnReason($code = null) {
		$array = array(
			'RETURN_DELIVERYFEE' => '반품 배송비 청구',
			'EXTRAFEEE' => '기타 반품 비용 청구',
			'RETURN_DELIVERYFEE_AND_EXTRAFEEE' => '반품 배송비 및 기타 반품 비용 청구',
			'RETURN_PRODUCT_NOT_DELIVERED' => '반품 상품 미입고',
			'ETC' => '기타 사유',
		);

		if($code) return $array[$code];

		return $array;
	}

	public function getWithholdExchangeReason($code = null) {
		$array = array(
			'EXCHANGE_DELIVERYFEE' => '교환 배송비 청구',
			'EXCHANGE_EXTRAFEE' => '기타 교환 비용 청구',
			'EXCHANGE_PRODUCT_READY' => '교환 상품 준비 중',
			'EXCHANGE_PRODUCT_NOT_DELIVERED' => '교환 상품 미입고',
			'EXCHANGE_HOLDBACK' => '교환 구매 확정 보류',
			'ETC' => '기타 사유',
		);

		if($code) return $array[$code];

		return $array;
	}

	public function getDelayedDispatchReason($code = null) {
		$array = array(
			'PRODUCT_PREPARE' => '상품 준비 중',
			'CUSTOMER_REQUEST' => '고객 요청',
			'CUSTOM_BUILD' => '주문 제작',
			'RESERVED_DISPATCH' => '예약 발송',
			'ETC' => '기타 사유',
		);

		if($code) return $array[$code];

		return $array;
	}


	public function getClaimInfo($nprd) {
		// 클레임 정보
		if(is_object($nprd->ClaimType)) {
			$ClaimType = $nprd->ClaimType->__toString();
		}
		$_ClaimType = ucfirst(strtolower($ClaimType));
		$reason = $this->getClaimReason($nprd->{$_ClaimType.'Reason'});
		if(is_object($nprd->{$_ClaimType.'DetailedReason'})) {
			$detailReason = $nprd->{$_ClaimType.'DetailedReason'}->__toString();
		}
		if(is_object($nprd->ClaimStatus)) {
			$ClaimStatus = $nprd->ClaimStatus->__toString();
		}

		switch($_ClaimType) {
			case 'Cancel' :
				$ClaimTypeName = '취소';
			break;
			case 'Return' :
				$ClaimTypeName = '반품';
			break;
			case 'Exchange' :
				$ClaimTypeName = '교환';
			break;
		}

		// 환불교환 배송비 정보
		if(is_object($nprd->ClaimDeliveryFeeDemandAmount)) {
			$ClaimDeliveryFeeDemandAmount = $nprd->ClaimDeliveryFeeDemandAmount->__toString();
		}
		if(is_object($nprd->ClaimDeliveryFeePayMethod)) {
			$ClaimDeliveryFeePayMethod = $nprd->ClaimDeliveryFeePayMethod->__toString();
		}

		// 보류 정보
		if(is_object($nprd->HoldbackStatus) == true) {
			$HoldbackStatus = $nprd->HoldbackStatus->__toString();

			if($HoldbackStatus == 'HOLDBACK') {
				if(is_object($nprd->HoldbackReason)) {
					$holdbackDetailReason = $nprd->HoldbackDetailedReason->__toString();
				}
				if(is_object($nprd->HoldbackReason)) {
					switch($nprd->HoldbackReason->__toString()) {
						case 'SELLER_CONFIRM_NEED' :
							$holdbackReason = '판매자 확인 필요';
						break;
						case 'PURCHASER_CONFIRM_NEED' :
							$holdbackReason = '구매자 확인 필요';
						break;
						case 'SELLER_REMIT' :
							$holdbackReason = '판매자 직접 송금';
						break;
						case 'RETURN_DELIVERYFEE' :
							$holdbackReason = '반품 배송비 청구';
						break;
						case 'EXTRAFEEE' :
							$holdbackReason = '기타 반품 비용 청구';
						break;
						case 'RETURN_DELIVERYFEE_AND_EXTRAFEEE' :
							$holdbackReason = '반품 배송비 및 기타 반품 비용 청구';
						break;
						case 'RETURN_PRODUCT_NOT_DELIVERED' :
							$holdbackReason = '반품 상품 미입고';
						break;
						case 'EXCHANGE_DELIVERYFEE' :
							$holdbackReason = '교환 배송비 청구';
						break;
						case 'EXCHANGE_EXTRAFEE' :
							$holdbackReason = '기타 교환 비용 청구';
						break;
						case 'EXCHANGE_PRODUCT_READY' :
							$holdbackReason = '교환 상품 준비 중';
						break;
						case 'EXCHANGE_PRODUCT_NOT_DELIVERED' :
							$holdbackReason = '교환 상품 미입고';
						break;
						case 'EXCHANGE_HOLDBACK' :
							$holdbackReason = '교환 구매 확정 보류';
						break;
						case 'ETC' :
							$holdbackReason = '기타 사유';
						break;
					}
				}
			}
		}

		// 환불 보류 상태
		if(is_object($nprd->RefundStandbyStatus)) {
			$RefundStandbyStatus =  $nprd->RefundStandbyStatus->__toString();
			if($RefundStandbyStatus != '환불처리완료') {
				$RefundStandbyReason =  $nprd->RefundStandbyReason->__toString();
			}
		}

		return array(
			'ClaimType' => $ClaimType, // 클레임 종류(코드)
			'ClaimReason' => $reason, // 클레임 사유
			'ClaimStatus' => $ClaimStatus, // 클레임 상태
			'ClaimdetailReason' => $detailReason, // 클레임 상세 사유
			'ClaimTypeName' => $ClaimTypeName, // 클레임 종류
			'HoldbackStatus' => $HoldbackStatus, // 보류 상태
			'HoldbackReason' => $holdbackReason, // 보류사유
			'HoldbackDetailReason' => $holdbackDetailReason, // 보류 상세사유
			'ClaimDeliveryFeePayMethod' => $ClaimDeliveryFeePayMethod, // 반품배송비 결제 방법
			'ClaimDeliveryFeeDemandAmount' => $ClaimDeliveryFeeDemandAmount, // 반품배송비
			'RefundStandbyStatus' => $RefundStandbyStatus, //환불보류상태
			'RefundStandbyReason' => $RefundStandbyReason, //환불보류사유
		);
	}

	// 네이버 요청에 따라 모든 배송처리시 PlaceProductOrder 선처리 및 배송지 정보 체크
	public function delivery($order_product_no, $dlv_code, $checkout_dlv_no) {
		global $tbl;

		$this->api('ShipProductOrder', $order_product_no, $dlv_code, $checkout_dlv_no);
		if($this->error) return false;
	}

	public function getReview($sdate, $edate, $classType = 'GENERAL') {
		global $cfg;

		$InquiryTimeFrom = $sdate;
		$InquiryTimeTo = $edate;

		return $this->api('GetPurchaseReviewList', $InquiryTimeFrom, $InquiryTimeTo, $cfg['checkout_id'], $classType);
	}

	public function getRevPt($pt) {
		return $pt;
	}

	public function getInquiry($sdate, $edate) {
		global $cfg;

		$InquiryTimeFrom = $sdate;
		$InquiryTimeTo = $edate;

		return $this->api('GetCustomerInquiryList', $InquiryTimeFrom, $InquiryTimeTo, $cfg['checkout_id']);
	}

	public function setInquiryAnswer($InquiryID, $AnswerContent, $AnswerContentID) {
		global $cfg;

		$this->api('AnswerCustomerInquiry', $InquiryID, $AnswerContent, $AnswerContentID, $cfg['checkout_id']);
	}

	public function error($msg) {
		$this->error = $msg;
		echo $this->error;
	}

    public function createLogTable()
    {
        global $tbl, $pdo;

        if (isTable($tbl['npay_api_log']) == true) return false;

        include __ENGINE_DIR__.'/_config/tbl_schema.php';
        return $pdo->query($tbl_schema['npay_api_log']);
    }

}

?>