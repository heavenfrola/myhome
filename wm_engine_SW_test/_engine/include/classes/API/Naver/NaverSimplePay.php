<?php

/**
 * 네이버 간편결제 및 정기/반복결제 API
 **/

namespace Wing\API\Naver;

use Wing\HTTP\CurlConnection;

class NaverSimplePay
{
    //const API_URL = 'dev.apis.naver.com';
    const API_URL = 'apis.naver.com';

    public function __construct($scfg, $is_subscr = 'N')
    {
        if ($is_subscr == 'Y') {
            $this->partnerId = $scfg->get('nsp_sub_partnerId');
            $this->clientId = $scfg->get('nsp_sub_clientId');
            $this->clientSecret = $scfg->get('nsp_sub_clientSecret');
        } else if ($is_subscr == 'A') {
            $this->partnerId = $scfg->get('nsp_sub_partnerId2');
            $this->clientId = $scfg->get('nsp_sub_clientId2');
            $this->clientSecret = $scfg->get('nsp_sub_clientSecret2');
        } else {
            $this->partnerId = $scfg->get('nsp_partnerId');
            $this->clientId = $scfg->get('nsp_clientId');
            $this->clientSecret = $scfg->get('nsp_clientSecret');
        }

        $this->db = &$GLOBALS['pdo'];
    }

    /**
     * 결제 승인
     **/
    public function payment($paymentId)
    {
        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/v2.2/apply/payment',
            'POST',
            'paymentId='.$paymentId
        );
        return json_decode($result);
    }

    /**
     * 결제 취소
     **/
    public function cancel($paymentId, $cancelRequester, $taxScopeAmount, $taxExScopeAmount, $cancelReason = null)
    {
        global $admin;

        if (empty($cancelReason) == true) {
            $cancelReason = '관리자에 의한 취소';
        }

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/v1/cancel',
            'POST',
            http_build_query(array(
                'paymentId' => $paymentId,
                'cancelRequester' => $cancelRequester,
                'cancelAmount' => ($taxScopeAmount+$taxExScopeAmount),
                'cancelReason' => $cancelReason,
                'taxScopeAmount' => $taxScopeAmount,
                'taxExScopeAmount' => $taxExScopeAmount
            ))
        );
        return json_decode($result);
    }

    /**
     * 에스크로 타입 가맹점인 경우 거래완료
     **/
    public function confirm($paymentId, $cancelRequester)
    {
        return;

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/v1/purchase-confirm',
            'POST',
            http_build_query(array(
                'paymentId' => $paymentId,
                'cancelRequester' => $cancelRequester,
            ))
        );
        return json_decode($result);
    }

    /**
     * 결제내역 조회
     **/
    public function listHistory($args1, $args2 = null)
    {
        if ($args2) { // 날짜 검색
            $query = json_encode(array(
                'startTime' => $args1,
                'endTime' => $args2,
            ));
        } else { // paymentId 검색
            $query = json_encode(array(
                'paymentId' => $args1,
            ));
        }

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/v2.2/list/history',
            'POST',
            $query,
            true
        );
        return json_decode($result);
    }

    /**
     * 정기결제 등록창 승인
     **/
    public function recurrentRegist($reserveId, $tempReceiptId)
    {
        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/recurrent/regist/v1/approval',
            'POST',
            http_build_query(array(
                'reserveId' => $reserveId,
                'tempReceiptId' => $tempReceiptId,
            ))
        );
        return json_decode($result);
    }

    /**
     * 정기결제 등록창 해지
     **/
    public function recurrentExpire($reserveId, $expireRequester = '2')
    {
        global $tbl;

        $stat = $this->db->row("select stat from {$tbl['subscription_key']} where recurrentId='$reserveId'");
        if ($stat == '2') {
            return null;
        }

        $expireReason = ($expireRequester == '1') ? '구매자에 의한 정기결제 해지' : '관리자에 의한 정기결제 해지';

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/recurrent/expire/v1/request?'.http_build_query(array(
                'recurrentId' => $reserveId,
                'expireRequester' => $expireRequester,
                'expireReason' => $expireReason,
            )),
            'POST',
            null
        );

        $this->db->query("update {$tbl['subscription_key']} set stat=2 where recurrentId='$reserveId'");

        return json_decode($result);
    }

    /**
     * 정기결제 등록 내역 조회
     **/
    public function recurrentList($args1, $args2)
    {
        if ($args2) { // 날짜 검색
            $query = json_encode(array(
                'startTime' => $args1,
                'endTime' => $args2,
            ));
        } else { // paymentId 검색
            $query = json_encode(array(
                'paymentId' => $args1,
            ));
        }

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/recurrent/v1/list',
            'POST',
            $query,
            true
        );
        return json_decode($result);
    }

    /**
     * 정기결제 예약
     **/
    public function recurrentReserve($paydata, $ono)
    {
        global $tbl;

        $sbono = $paydata['sbono'];
        $key = $this->db->assoc("select * from {$tbl['subscription_key']} where ono=?", array($sbono));
        $card = $this->db->assoc(
            "select wm_ono, good_name, member_no, guest_no from {$tbl['card']} where wm_ono=?",
               array($sbono)
        );

        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/recurrent/pay/v3/reserve',
            'POST',
            http_build_query(array(
                'recurrentId' => $key['recurrentId'],
                'totalPayAmount' => parsePrice($paydata['total_prc']),
                'taxScopeAmount' => parsePrice($paydata['total_prc']-$paydata['taxfree_prc']),
                'taxExScopeAmount' => parsePrice($paydata['taxfree_prc']),
                'productName' => $card['good_name'],
                'merchantPayId' => $paydata['sbono'].'_'.$paydata['no'],
                'merchantUserId' => ($card['member_no']) ? 'm'.sprintf('%06d', $card['member_no']) : 'g'.$card['guest_no']
            ))
        );
        return json_decode($result);
    }

    /**
     * 정기결제 승인
     **/
    public function recurrentApproval($recurrentId, $paymentId)
    {
        $result = $this->api(
            'https://'.SELF::API_URL.'/'.$this->partnerId.'/naverpay/payments/recurrent/pay/v3/approval',
            'POST',
            http_build_query(array(
                'recurrentId' => $recurrentId,
                'paymentId' => $paymentId,
            ))
        );
        return json_decode($result);
    }

    /**
     * API 인증 헤더를 이용하여 데이터 전송
     **/
    private function api($url, $method, $param, $is_json = false)
    {
        global $log_instance, $scfg;

        $headers = array(
            'X-Naver-Client-Id: '.$this->clientId,
            'X-Naver-Client-Secret: '.$this->clientSecret,
        );
        if ($scfg->get('nsp_chainId')) {
            $headers[] = 'X-NaverPay-Chain-Id: '.$scfg->get('nsp_chainId');
        }
        if ($is_json == true) {
            $headers[] = 'Content-Type: application/json';
        }

        $curl = new CurlConnection($url, $method, $param);
        $curl->setHeader($headers);

        $curl->exec();
        $result = $curl->getResult(true);

        // 주문 로그
        if (is_object($log_instance) == true && method_exists($log_instance, 'writeln') == true) {
            $log_instance->writeln($param."\n".$result, '[nsp] '.$url);
        }

        return $result;
    }

    /**
     * 에러 메시지 변환
     **/
    public function resultMessage($resultMessage)
    {
        switch($resultMessage) {
            case 'userCancel' :
                return '결제를 취소하셨습니다.주문 내용 확인 후 다시 결제해주세요.';
                break;
            case 'OwnerAuthFail' :
                return '타인 명의 카드는 결제가 불가능합니다.회원 본인 명의의 카드로 결제해주세요.';
                break;
            case 'paymentTimeExpire' :
                return '결제 가능한 시간이 지났습니다.주문 내용 확인 후 다시 결제해주세요.';
                break;
            case 'webhookFail' :
                return '호출 응답 실패';
                break;
        }
        return $resultMessage;
    }

    /**
     * 카드사명 매칭
     **/
    public function getCardName($code)
    {
        $_codes = array(
            'C0' => '신한', 'C1' => '비씨', 'C2' => '광주', 'C3' => 'KB국민', 'C4' => 'NH',
            'C5' => '롯데', 'C6' => '산업', 'C7' => '삼성', 'C8' => '수협', 'C9' => '씨티',
            'CA' => '외환', 'CB' => '우리', 'CC' => '전북', 'CD' => '제주', 'CF' => '하나-외환',
            'CH' => '현대',
        );
        return $_codes[$code];
    }

    /**
     * 은행명 매칭
     **/
    public function getBankName($code)
    {
        $_codes = array(
            '002' => '산업은행', '003' => '기업은행', '004' => '국민은행', '005' => '외환은행', '007' => '수협',
            '011' => '농협', '012' => '지역농·축협', '020' => '우리은행', '023' => 'SC제일은행', '027' => '씨티은행',
            '031' => '대구은행', '032' => '부산은행', '034' => '광주은행', '035' => '제주은행', '037' => '전북은행',
            '039' => '경남은행', '045' => '새마을금고', '048' => '신협', '050' => '저축은행', '071' => '우체국',
            '081' => '하나은행', '088' => '신한은행', '089' => '케이뱅크', '090' => '카카오뱅크', '102' => '대신저축은행',
            '103' => '에스비아이저축은행', '104' => '에이치케이저축은행', '105' => '웰컴저축은행', '106' => '신한저축은행', '209' => '유안타증권',
            '218' => 'KB증권', '221' => '상상인증권', '222' => '한양증권', '223' => '리딩투자증권', '224' => 'BNK투자증권',
            '225' => 'IBK투자증권', '227' => 'KTB투자증권', '230' => '미래에셋', '238' => '미래에셋', '240' => '삼성증권',
            '243' => '한국투자증권', '247' => 'NH투자증권', '261' => '교보증권', '262' => '하이투자증권', '263' => '현대차증권',
            '264' => '키움증권', '265' => '이베스트투자증권', '266' => 'SK증권', '267' => '대신증권', '269' => '한화증권',
            '270' => '하나금융투자', '278' => '신한금융투자', '279' => 'DB금융투자', '280' => '유진투자증권',
            '287' => '메리츠증권',  '288' => '카카오페이증권', '290' => '부국증권','291' => '신영증권',
            '292' => '케이프투자증권',  '293' => '한국증권금융', '294' => '한국포스증권', '295' => '우리종합금융',
        );
        return $_codes[$code];
    }

}