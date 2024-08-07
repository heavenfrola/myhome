<?PHP

	Abstract class ErpInterface {

		// 상품등록, 수정시 이벤트
		// 상품번호[, 변경전 상품상태]
		abstract public function setProduct($pno, $ori_stat = null);

		// 회원가입, 탈퇴, 정보 수정시 이벤트
		abstract public function setChangedMember();

		// 적립금 정보 변경시 이벤트
		// 적립금 로그DB 데이터[, milage or emoney]
		abstract public function setMilage($param, $type = 'milage');

		// 쿠폰 발급, 사용시 이벤트
		// wm_coupon_download.no
		abstract public function setCoupon($cno);

		// 쿠폰 삭제시 이벤트
		// wm_coupon_download.no
		abstract public function removeCoupon($cno);

		// 주문 및 상태변경시 이벤트
		abstract public function setOrder($ono = null);

		// 주문서 삭제시 이벤트
		// 주문번호
		abstract public function removeOrder($ono);

		// ERP로부터 재고 수집 요청
		// 반품 교환등 발생시 해당 주문번호에 있는 상품들의 개별 재고를 실시간 재수집
		abstract public function getStock($sku);

	}

?>