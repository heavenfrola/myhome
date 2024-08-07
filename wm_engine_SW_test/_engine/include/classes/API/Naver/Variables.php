<?php

/**
 * Variables
 * PHP 5.x 에서 배열을 상수로 사용할 수 없어 멤버 변수에서 리턴하도록 대체
 * 차후 다른 네이버 서비스에서 이용 가능
 */

namespace Wing\API\Naver;

Trait Variables {

    /**
     * 클레임 타입 배열 리턴
     * @return array
     */
    protected function vClaimType()
    {
        return array(
            'CANCEL' => '취소',
            'RETURN' => '반품',
            'EXCHANGE' => '교환',
            'PURCHASE_DECISION_HOLDBACK' => '구매 확정 보류',
            'ADMIN_CANCEL' => '직권 취소'
        );
    }

    /**
     * 취소 사유 배열 리턴
     * @return array
     */
    protected function vCancelReason()
    {
        return array(
            'INTENT_CHANGED' => '구매 의사 취소',
            'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
            'WRONG_ORDER' => '다른 상품 잘못 주문',
            'PRODUCT_UNSATISFIED' => '서비스 불만족',
            'DELAYED_DELIVERY' => '배송 지연',
            'SOLD_OUT' => '상품 품절',
            'DROPPED_DELIVERY' => '배송 누락',
            'NOT_YET_DELIVERY' => '미배송',
            'BROKEN' => '상품 파손',
            'INCORRECT_INFO' => '상품 정보 상이',
            'WRONG_DELIVERY' => '오배송',
            'WRONG_OPTION' => '색상 등 다른 상품 잘못 배송',
            'SIMPLE_INTENT_CHANGED' => '단순 변심',
            'MISTAKE_ORDER' => '주문 실수',
            'ETC' => '기타',
            'DELAYED_DELIVERY_BY_PURCHASER' => '배송 지연',
            'INCORRECT_INFO_BY_PURCHASER' => '상품 정보 상이',
            'PRODUCT_UNSATISFIED_BY_PURCHASER' => '서비스 불만족',
            'NOT_YET_DISCUSSION' => '상호 협의가 완료되지 않은 주문 건',
            'OUT_OF_STOCK' => '재고 부족으로 인한 판매 불가',
            'SALE_INTENT_CHANGED' => '판매 의사 변심으로 인한 거부',
            'NOT_YET_PAYMENT' => '구매자의 미결제로 인한 거부',
            'NOT_YET_RECEIVE' => '상품 미수취',
            'WRONG_DELAYED_DELIVERY' => '오배송 및 지연',
            'BROKEN_AND_BAD' => '파손 및 불량',
            'RECEIVING_DUE_DATE_OVER' => '수락 기한 만료',
            'RECEIVER_MISMATCHED' => '수신인 불일치',
            'GIFT_INTENT_CHANGED' => '보내기 취소',
            'GIFT_REFUSAL' => '선물 거절',
            'MINOR_RESTRICTED' => '상품 수신 불가',
            'RECEIVING_BLOCKED' => '상품 수신 불가',
            'UNDER_QUANTITY' => '주문 수량 미달',
            'ASYNC_FAIL_PAYMENT' => '결제 승인 실패',
            'ASYNC_LONG_WAIT_PAYMENT' => '결제 승인 실패'
        );
    }

    /**
     * 반품 사유 배열 리턴
     * @return array
     */
    protected function vReturnReason()
    {
        return array(
            'INTENT_CHANGED' => '구매 의사 취소',
            'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
            'WRONG_ORDER' => '다른 상품 잘못 주문',
            'PRODUCT_UNSATISFIED' => '서비스 불만족',
            'DELAYED_DELIVERY' => '배송 지연',
            'SOLD_OUT' => '상품 품절',
            'DROPPED_DELIVERY' => '배송 누락',
            'NOT_YET_DELIVERY' => '미배송',
            'BROKEN' => '상품 파손',
            'INCORRECT_INFO' => '상품 정보 상이',
            'WRONG_DELIVERY' => '오배송',
            'WRONG_OPTION' => '색상 등 다른 상품 잘못 배송',
            'SIMPLE_INTENT_CHANGED' => '단순 변심',
            'MISTAKE_ORDER' => '주문 실수',
            'ETC' => '기타',
            'DELAYED_DELIVERY_BY_PURCHASER' => '배송 지연',
            'INCORRECT_INFO_BY_PURCHASER' => '상품 정보 상이',
            'PRODUCT_UNSATISFIED_BY_PURCHASER' => '서비스 불만족',
            'NOT_YET_DISCUSSION' => '상호 협의가 완료되지 않은 주문 건',
            'OUT_OF_STOCK' => '재고 부족으로 인한 판매 불가',
            'SALE_INTENT_CHANGED' => '판매 의사 변심으로 인한 거부',
            'NOT_YET_PAYMENT' => '구매자의 미결제로 인한 거부',
            'NOT_YET_RECEIVE' => '상품 미수취',
            'WRONG_DELAYED_DELIVERY' => '오배송 및 지연',
            'BROKEN_AND_BAD' => '파손 및 불량',
            'RECEIVING_DUE_DATE_OVER' => '수락 기한 만료',
            'RECEIVER_MISMATCHED' => '수신인 불일치',
            'GIFT_INTENT_CHANGED' => '보내기 취소',
            'GIFT_REFUSAL' => '선물 거절',
            'MINOR_RESTRICTED' => '상품 수신 불가',
            'RECEIVING_BLOCKED' => '상품 수신 불가',
            'UNDER_QUANTITY' => '주문 수량 미달',
            'ASYNC_FAIL_PAYMENT' => '결제 승인 실패',
            'ASYNC_LONG_WAIT_PAYMENT' => '결제 승인 실패'
        );
    }

    /**
     * 교환 사유 배열 리턴
     * @return array
     */
    protected function vExchangeReason()
    {
        return array(
            'INTENT_CHANGED' => '구매 의사 취소',
            'COLOR_AND_SIZE' => '색상 및 사이즈 변경',
            'WRONG_ORDER' => '다른 상품 잘못 주문',
            'PRODUCT_UNSATISFIED' => '서비스 불만족',
            'DELAYED_DELIVERY' => '배송 지연',
            'SOLD_OUT' => '상품 품절',
            'DROPPED_DELIVERY' => '배송 누락',
            'NOT_YET_DELIVERY' => '미배송',
            'BROKEN' => '상품 파손',
            'INCORRECT_INFO' => '상품 정보 상이',
            'WRONG_DELIVERY' => '오배송',
            'WRONG_OPTION' => '색상 등 다른 상품 잘못 배송',
            'SIMPLE_INTENT_CHANGED' => '단순 변심',
            'MISTAKE_ORDER' => '주문 실수',
            'ETC' => '기타',
            'DELAYED_DELIVERY_BY_PURCHASER' => '배송 지연',
            'INCORRECT_INFO_BY_PURCHASER' => '상품 정보 상이',
            'PRODUCT_UNSATISFIED_BY_PURCHASER' => '서비스 불만족',
            'NOT_YET_DISCUSSION' => '상호 협의가 완료되지 않은 주문 건',
            'OUT_OF_STOCK' => '재고 부족으로 인한 판매 불가',
            'SALE_INTENT_CHANGED' => '판매 의사 변심으로 인한 거부',
            'NOT_YET_PAYMENT' => '구매자의 미결제로 인한 거부',
            'NOT_YET_RECEIVE' => '상품 미수취',
            'WRONG_DELAYED_DELIVERY' => '오배송 및 지연',
            'BROKEN_AND_BAD' => '파손 및 불량',
            'RECEIVING_DUE_DATE_OVER' => '수락 기한 만료',
            'RECEIVER_MISMATCHED' => '수신인 불일치',
            'GIFT_INTENT_CHANGED' => '보내기 취소',
            'GIFT_REFUSAL' => '선물 거절',
            'MINOR_RESTRICTED' => '상품 수신 불가',
            'RECEIVING_BLOCKED' => '상품 수신 불가',
            'UNDER_QUANTITY' => '주문 수량 미달',
            'ASYNC_FAIL_PAYMENT' => '결제 승인 실패',
            'ASYNC_LONG_WAIT_PAYMENT' => '결제 승인 실패'
        );
    }

    /**
     * 발송 지역 사유 벼열 리턴
     * @return array
     */
    protected function vdelayedDispatchReason()
    {
        return array(
            'PRODUCT_PREPARE' => '상품 준비 중',
            'CUSTOMER_REQUEST' => '고객 요청',
            'CUSTOM_BUILD' => '주문 제작',
            'RESERVED_DISPATCH' => '예약 발송',
            'OVERSEA_DELIVERY' => '해외 배송',
            'ETC' => '기타'
        );
    }

}